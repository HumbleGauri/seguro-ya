<?php
/**
 * Helper para registrar cada intento de acceso en la tabla de auditoría.
 * Se usa tanto en login exitoso como fallido.
 */

function registrar_acceso(PDO $pdo, ?int $usuarioId, string $resultado, ?string $emailIntentado = null): void
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? null;

    $stmt = $pdo->prepare(
        'INSERT INTO log_accesos (usuario_id, email_intentado, fecha_hora, resultado, ip)
         VALUES (:usuario_id, :email_intentado, NOW(), :resultado, :ip)'
    );
    $stmt->execute([
        'usuario_id'      => $usuarioId,
        'email_intentado' => $emailIntentado,
        'resultado'       => $resultado, // 'exitoso' | 'fallido'
        'ip'              => $ip,
    ]);
}
