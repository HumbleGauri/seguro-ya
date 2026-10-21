<?php
/** POST: usuario, vehiculo y seguro; alta en una única transacción. */
require __DIR__ . '/../includes/bootstrap.php';
require __DIR__ . '/../includes/registration.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('Método no permitido.', 405);
}

try {
    $registro = validar_registro(leer_body());
    $usuario = $registro['usuario'];
    $resultado = crear_registro_completo($pdo, $registro);
} catch (InvalidArgumentException $error) {
    json_error($error->getMessage());
} catch (PDOException $error) {
    if (($error->errorInfo[1] ?? null) === 1062) {
        json_error('El nombre de usuario, correo, DNI, patente o póliza de esa aseguradora ya está registrado. Revisá los datos.', 409);
    }
    error_log('Registro: SQLSTATE=' . $error->getCode() . '; codigo=' . ($error->errorInfo[1] ?? 'desconocido'));
    json_error('No se pudo completar el registro. Intentá nuevamente.', 500);
} catch (Throwable $error) {
    json_error('No se pudo completar el registro. Intentá nuevamente.', 500);
}

json_ok($resultado + [
    'nombre_usuario' => $usuario['nombre_usuario'],
    'email' => $usuario['email'],
    'mensaje' => 'Usuario, vehículo y seguro registrados correctamente.',
]);
