<?php
function obtener_credencial(PDO $pdo, int $usuarioId): ?array
{
    $stmt = $pdo->prepare('SELECT c.id, c.numero_credencial, CONCAT(u.nombres, \' \', u.apellidos) AS asegurado_nombre,
        u.fecha_nacimiento FROM credenciales c JOIN usuarios u ON u.id=c.usuario_id WHERE c.usuario_id=:usuario_id');
    $stmt->execute(['usuario_id' => $usuarioId]);
    $credencial = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$credencial) return null;
    $stmt = $pdo->prepare('SELECT s.id, s.nro_poliza, s.cobertura, s.vigencia_desde, s.vigencia_hasta, s.estado,
        a.nombre AS aseguradora, v.patente, v.marca, v.modelo, v.anio, v.chasis, v.motor
        FROM seguros s JOIN vehiculos v ON v.id=s.vehiculo_id AND v.usuario_id=s.usuario_id
        JOIN aseguradoras a ON a.id=s.aseguradora_id
        WHERE s.credencial_id=:credencial_id AND s.usuario_id=:usuario_id ORDER BY s.id');
    $stmt->execute(['credencial_id' => $credencial['id'], 'usuario_id' => $usuarioId]);
    $credencial['seguros'] = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $fila) {
        $fila['id'] = (int)$fila['id'];
        $fila['vehiculo'] = [];
        foreach (['patente','marca','modelo','anio','chasis','motor'] as $campo) {
            $fila['vehiculo'][$campo] = $campo === 'anio' && $fila[$campo] !== null ? (int)$fila[$campo] : $fila[$campo];
            unset($fila[$campo]);
        }
        $credencial['seguros'][] = $fila;
    }
    unset($credencial['id']);
    return $credencial;
}
