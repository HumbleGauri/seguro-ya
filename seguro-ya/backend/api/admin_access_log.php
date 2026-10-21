<?php

require __DIR__ . '/../includes/bootstrap.php';

requiere_admin();

$stmt = $pdo->query(
    'SELECT la.id, la.fecha_hora, la.resultado, la.ip,
            COALESCE(u.email, la.email_intentado) AS email
     FROM log_accesos la
     LEFT JOIN usuarios u ON u.id = la.usuario_id
     ORDER BY la.fecha_hora DESC'
);
$log = $stmt->fetchAll();

json_ok(['log' => $log]);
