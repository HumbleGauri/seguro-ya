<?php
/** Validación y persistencia del alta conjunta. */
function validar_registro(array $body): array
{
    $usuario = [];
    foreach (['nombre_usuario', 'nombres', 'apellidos', 'dni', 'fecha_nacimiento', 'email', 'password'] as $campo) {
        if (!isset($body[$campo]) || !is_string($body[$campo])) {
            throw new InvalidArgumentException('Completá todos los datos del usuario.');
        }
        $usuario[$campo] = $campo === 'password' ? $body[$campo] : trim($body[$campo]);
        if ($usuario[$campo] === '') {
            throw new InvalidArgumentException('Completá todos los datos del usuario.');
        }
    }
    if (!validar_nombre_usuario($usuario['nombre_usuario'])) {
        throw new InvalidArgumentException('El nombre de usuario debe tener entre 3 y 50 caracteres (letras, números, "." o "_").');
    }
    if (mb_strlen($usuario['nombres']) > 100 || mb_strlen($usuario['apellidos']) > 100) {
        throw new InvalidArgumentException('Nombres y apellidos no pueden superar los 100 caracteres.');
    }
    if (!preg_match('/^[0-9]{7,8}$/', $usuario['dni'])) {
        throw new InvalidArgumentException('El DNI debe tener 7 u 8 dígitos, sin puntos ni espacios.');
    }
    if (strlen($usuario['email']) > 255 || !validar_email($usuario['email'])) {
        throw new InvalidArgumentException('El email no tiene un formato válido.');
    }
    if (!validar_password_fuerte($usuario['password'])) {
        throw new InvalidArgumentException('La contraseña debe tener mínimo 8 caracteres, una mayúscula, un número y un símbolo.');
    }
    if (!validar_fecha_nacimiento($usuario['fecha_nacimiento'])) {
        throw new InvalidArgumentException('La fecha de nacimiento debe tener el formato YYYY-MM-DD.');
    }

    if (!isset($body['vehiculo']) || !is_array($body['vehiculo'])) {
        throw new InvalidArgumentException('Completá los datos del vehículo.');
    }
    $vehiculo = [];
    foreach (['marca' => 50, 'modelo' => 50, 'patente' => 10, 'chasis' => 50, 'motor' => 50] as $campo => $maximo) {
        $valor = $body['vehiculo'][$campo] ?? null;
        if (!is_string($valor) || trim($valor) === '' || mb_strlen(trim($valor)) > $maximo) {
            throw new InvalidArgumentException('Completá ' . $campo . ' del vehículo (máximo ' . $maximo . ' caracteres).');
        }
        $vehiculo[$campo] = trim($valor);
    }
    $vehiculo['patente'] = strtoupper($vehiculo['patente']);
    if (!preg_match('/^[A-Z0-9]{1,10}$/', $vehiculo['patente'])) {
        throw new InvalidArgumentException('La patente debe tener hasta 10 letras o números, sin espacios.');
    }
    $anio = $body['vehiculo']['anio'] ?? null;
    if ((!is_int($anio) && !is_string($anio)) || !preg_match('/^[0-9]{4}$/', (string)$anio) || (int)$anio < 1900 || (int)$anio > 2155) {
        throw new InvalidArgumentException('El año del vehículo debe estar entre 1900 y 2155.');
    }
    $vehiculo['anio'] = (int)$anio;

    if (!isset($body['seguro']) || !is_array($body['seguro'])) {
        throw new InvalidArgumentException('Completá los datos del seguro.');
    }
    $seguro = [];
    foreach (['aseguradora' => 100, 'nro_poliza' => 50, 'cobertura' => 100, 'vigencia_desde' => 10, 'vigencia_hasta' => 10] as $campo => $maximo) {
        $valor = $body['seguro'][$campo] ?? null;
        if (!is_string($valor) || trim($valor) === '' || mb_strlen(trim($valor)) > $maximo) {
            throw new InvalidArgumentException('Completá ' . $campo . ' del seguro (máximo ' . $maximo . ' caracteres).');
        }
        $seguro[$campo] = trim($valor);
    }
    if (!in_array($body['seguro']['cobertura'], ['Terceros Básico', 'Terceros Completo', 'Todo Riesgo con Franquicia'], true)) {
        throw new InvalidArgumentException('Seleccioná una cobertura válida: Terceros Básico, Terceros Completo o Todo Riesgo con Franquicia.');
    }
    if (!validar_fecha_nacimiento($seguro['vigencia_desde']) || !validar_fecha_nacimiento($seguro['vigencia_hasta'])) {
        throw new InvalidArgumentException('Las fechas de vigencia deben tener el formato YYYY-MM-DD.');
    }
    if ($seguro['vigencia_hasta'] < $seguro['vigencia_desde']) {
        throw new InvalidArgumentException('La vigencia hasta no puede ser anterior a la vigencia desde.');
    }
    return ['usuario' => $usuario, 'vehiculo' => $vehiculo, 'seguro' => $seguro];
}

function crear_registro_completo(PDO $pdo, array $registro): array
{
    $usuario = $registro['usuario'];
    $vehiculo = $registro['vehiculo'];
    $hash = hashear_password($usuario['password']);
    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare(
            'INSERT INTO usuarios (nombre_usuario, email, password_hash, perfil, nombres, apellidos, dni, fecha_nacimiento)
             VALUES (:nombre_usuario, :email, :password_hash, :perfil, :nombres, :apellidos, :dni, :fecha_nacimiento)'
        );
        $stmt->execute([
            'nombre_usuario' => $usuario['nombre_usuario'], 'email' => $usuario['email'],
            'password_hash' => $hash, 'perfil' => 'usuario', 'nombres' => $usuario['nombres'],
            'apellidos' => $usuario['apellidos'], 'dni' => $usuario['dni'],
            'fecha_nacimiento' => $usuario['fecha_nacimiento'],
        ]);
        $usuarioId = (int)$pdo->lastInsertId();
        $stmt = $pdo->prepare(
            'INSERT INTO vehiculos (usuario_id, marca, modelo, anio, patente, chasis, motor)
             VALUES (:usuario_id, :marca, :modelo, :anio, :patente, :chasis, :motor)'
        );
        $stmt->execute(['usuario_id' => $usuarioId] + $vehiculo);
        $vehiculoId = (int)$pdo->lastInsertId();
        $stmt = $pdo->prepare('INSERT INTO credenciales (usuario_id, numero_credencial) VALUES (:usuario_id, :numero_credencial)');
        $stmt->execute(['usuario_id' => $usuarioId, 'numero_credencial' => 'CRED-' . bin2hex(random_bytes(15))]);
        $credencialId = (int)$pdo->lastInsertId();

        // Reutiliza el catálogo por su nombre único sin reactivar compañías inactivas.
        $stmt = $pdo->prepare('INSERT INTO aseguradoras (nombre) VALUES (:nombre) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id)');
        $stmt->execute(['nombre' => $registro['seguro']['aseguradora']]);
        $aseguradoraId = (int)$pdo->lastInsertId();
        $stmt = $pdo->prepare('SELECT estado FROM aseguradoras WHERE id = :id FOR UPDATE');
        $stmt->execute(['id' => $aseguradoraId]);
        if ($stmt->fetchColumn() !== 'activa') {
            throw new InvalidArgumentException('La aseguradora seleccionada está inactiva.');
        }

        $stmt = $pdo->prepare(
            'INSERT INTO seguros (usuario_id, credencial_id, vehiculo_id, aseguradora_id, nro_poliza, cobertura, vigencia_desde, vigencia_hasta)
             VALUES (:usuario_id, :credencial_id, :vehiculo_id, :aseguradora_id, :nro_poliza, :cobertura, :vigencia_desde, :vigencia_hasta)'
        );
        $seguro = $registro['seguro'];
        unset($seguro['aseguradora']);
        $stmt->execute(['usuario_id' => $usuarioId, 'credencial_id' => $credencialId,
            'vehiculo_id' => $vehiculoId, 'aseguradora_id' => $aseguradoraId] + $seguro);
        $seguroId = (int)$pdo->lastInsertId();
        $pdo->commit();
        return ['id' => $usuarioId, 'vehiculo_id' => $vehiculoId, 'credencial_id' => $credencialId, 'seguro_id' => $seguroId];
    } catch (Throwable $error) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $error;
    }
}
