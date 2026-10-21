<?php

const RECOVERY_INVALID_TOKEN = 'El token es inválido, venció o ya fue utilizado.';
const RECOVERY_REQUEST_MESSAGE = 'Si el correo está registrado, un administrador podrá enviarte el token de recuperación. Ingresalo para continuar.';

function recovery_email(array $body): string
{
    if (!isset($body['email']) || !is_string($body['email'])) {
        throw new InvalidArgumentException('Ingresá un email válido.');
    }
    $email = trim($body['email']);
    if (strlen($email) > 255 || !validar_email($email)) {
        throw new InvalidArgumentException('Ingresá un email válido.');
    }
    return $email;
}

function recovery_token(array $body): string
{
    if (!isset($body['token']) || !is_string($body['token'])) {
        throw new InvalidArgumentException(RECOVERY_INVALID_TOKEN);
    }
    $token = trim($body['token']);
    if ($token === '' || strlen($token) > 64) {
        throw new InvalidArgumentException(RECOVERY_INVALID_TOKEN);
    }
    return $token;
}

// Las operaciones que escriben bloquean primero al usuario y luego su recupero.
function recovery_user(PDO $pdo, string $email, bool $lock = false)
{
    $stmt = $pdo->prepare('SELECT id FROM usuarios WHERE email = :email LIMIT 1' . ($lock ? ' FOR UPDATE' : ''));
    $stmt->execute(['email' => $email]);
    return $stmt->fetch();
}

function request_password_recovery(PDO $pdo, string $email): void
{
    $pdo->beginTransaction();
    try {
        $user = recovery_user($pdo, $email, true);
        if ($user) {
            $stmt = $pdo->prepare(
                'SELECT id FROM recuperacion_password
                 WHERE usuario_id = :usuario_id AND usado = 0 AND expira > CURRENT_TIMESTAMP
                 ORDER BY id DESC LIMIT 1 FOR UPDATE'
            );
            $stmt->execute(['usuario_id' => $user['id']]);
            if (!$stmt->fetch()) {
                $stmt = $pdo->prepare(
                    'INSERT INTO recuperacion_password (usuario_id, token, expira, usado)
                     VALUES (:usuario_id, :token, DATE_ADD(CURRENT_TIMESTAMP, INTERVAL 30 MINUTE), 0)'
                );
                $stmt->execute(['usuario_id' => $user['id'], 'token' => bin2hex(random_bytes(16))]);
            }
        }
        $pdo->commit();
    } catch (Throwable $error) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        throw $error;
    }
}

function find_valid_recovery(PDO $pdo, int $userId, string $token, bool $lock = false): array
{
    $stmt = $pdo->prepare(
        'SELECT id, token FROM recuperacion_password
         WHERE usuario_id = :usuario_id AND token = :token AND usado = 0
           AND expira > CURRENT_TIMESTAMP LIMIT 1' . ($lock ? ' FOR UPDATE' : '')
    );
    $stmt->execute(['usuario_id' => $userId, 'token' => $token]);
    $recovery = $stmt->fetch();
    // La colación de la BD puede ignorar mayúsculas; el token debe ser idéntico.
    if (!$recovery || !hash_equals($recovery['token'], $token)) {
        throw new InvalidArgumentException(RECOVERY_INVALID_TOKEN);
    }
    return $recovery;
}

function verify_password_recovery(PDO $pdo, string $email, string $token): void
{
    $user = recovery_user($pdo, $email);
    if (!$user) throw new InvalidArgumentException(RECOVERY_INVALID_TOKEN);
    find_valid_recovery($pdo, (int) $user['id'], $token);
}

function reset_recovered_password(PDO $pdo, string $email, string $token, string $password): void
{
    $hash = hashear_password($password);
    $pdo->beginTransaction();
    try {
        $user = recovery_user($pdo, $email, true);
        if (!$user) throw new InvalidArgumentException(RECOVERY_INVALID_TOKEN);
        $recovery = find_valid_recovery($pdo, (int) $user['id'], $token, true);

        // Revalidar al consumir: pudo vencer mientras se esperaba el bloqueo.
        $stmt = $pdo->prepare(
            'UPDATE recuperacion_password SET usado = 1
             WHERE id = :id AND usado = 0 AND expira > CURRENT_TIMESTAMP'
        );
        $stmt->execute(['id' => $recovery['id']]);
        if ($stmt->rowCount() !== 1) throw new InvalidArgumentException(RECOVERY_INVALID_TOKEN);

        $stmt = $pdo->prepare('UPDATE usuarios SET password_hash = :hash WHERE id = :id');
        $stmt->execute(['hash' => $hash, 'id' => $user['id']]);
        $pdo->commit();
    } catch (Throwable $error) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        throw $error;
    }
}

function list_recovery_requests(PDO $pdo): array
{
    return $pdo->query(
        "SELECT r.id, r.usuario_id, u.nombre_usuario, u.email, r.token,
                r.fecha_creacion, r.expira, r.usado,
                CASE WHEN r.usado = 1 THEN 'utilizada'
                     WHEN r.expira <= CURRENT_TIMESTAMP THEN 'vencida'
                     ELSE 'pendiente' END AS estado
         FROM recuperacion_password r
         INNER JOIN usuarios u ON u.id = r.usuario_id
         ORDER BY r.fecha_creacion DESC, r.id DESC"
    )->fetchAll();
}
