<?php
require_once __DIR__ . '/db.php';

function ensure_admin_profile_columns(): void {
    static $done = false;
    if ($done) return;
    $done = true;
    $pdo = db();
    try {
        $stmt = $pdo->query('SHOW COLUMNS FROM admin_users');
        $columns = array_map(static fn($row) => $row['Field'] ?? '', $stmt->fetchAll());
        if (!in_array('avatar', $columns, true)) {
            $pdo->exec('ALTER TABLE admin_users ADD avatar VARCHAR(255) NULL AFTER name');
        }
        if (!in_array('last_login_at', $columns, true)) {
            $pdo->exec('ALTER TABLE admin_users ADD last_login_at DATETIME NULL AFTER is_active');
        }
        if (!in_array('password_updated_at', $columns, true)) {
            $pdo->exec('ALTER TABLE admin_users ADD password_updated_at DATETIME NULL AFTER last_login_at');
        }
    } catch (Throwable $e) {
        // Keep admin pages usable even if the hosting DB user cannot run ALTER TABLE.
    }
}

function is_admin_logged_in(): bool {
    return !empty($_SESSION['admin_user_id']);
}

function require_admin(): void {
    if (!is_admin_logged_in()) {
        header('Location: login.php');
        exit;
    }
    enforce_admin_session_security();
}

function admin_fetch_current(): ?array {
    if (empty($_SESSION['admin_user_id'])) return null;
    ensure_admin_profile_columns();
    try {
        $stmt = db()->prepare('SELECT * FROM admin_users WHERE id = ? AND is_active = 1 LIMIT 1');
        $stmt->execute([(int)$_SESSION['admin_user_id']]);
        $admin = $stmt->fetch();
        return $admin ?: null;
    } catch (Throwable $e) {
        return null;
    }
}

function admin_refresh_session(array $admin): void {
    $_SESSION['admin_user_id'] = (int)$admin['id'];
    $_SESSION['admin_name'] = $admin['name'] ?: $admin['username'];
    $_SESSION['admin_username'] = $admin['username'];
    $_SESSION['admin_avatar'] = $admin['avatar'] ?? '';
}

function current_admin_name(): string {
    return $_SESSION['admin_name'] ?? 'Admin';
}

function current_admin_username(): string {
    return $_SESSION['admin_username'] ?? 'admin';
}

function current_admin_avatar(): string {
    return $_SESSION['admin_avatar'] ?? '';
}

function admin_initials(string $name): string {
    $name = trim($name);
    if ($name === '') return 'A';
    $parts = preg_split('/\s+/', $name) ?: [];
    $letters = '';
    foreach (array_slice($parts, 0, 2) as $part) {
        $letters .= strtoupper(substr($part, 0, 1));
    }
    return $letters ?: strtoupper(substr($name, 0, 1));
}

function admin_login(string $username, string $password): bool {
    ensure_admin_profile_columns();
    ensure_phase6_security_schema();
    $username = trim($username);
    if (login_attempts_blocked($username)) {
        throw new RuntimeException('Too many failed login attempts. Please wait 15 minutes and try again.');
    }
    $pdo = db();
    $stmt = $pdo->prepare('SELECT * FROM admin_users WHERE username = ? AND is_active = 1 LIMIT 1');
    $stmt->execute([$username]);
    $admin = $stmt->fetch();
    if (!$admin || !password_verify($password, $admin['password_hash'])) {
        record_login_attempt($username, false);
        return false;
    }
    session_regenerate_id(true);
    admin_refresh_session($admin);
    $_SESSION['admin_last_seen'] = time();
    $_SESSION['admin_fingerprint'] = hash('sha256', client_ip() . '|' . security_user_agent());
    record_login_attempt($username, true);
    try {
        $pdo->prepare('UPDATE admin_users SET last_login_at = NOW() WHERE id = ?')->execute([(int)$admin['id']]);
    } catch (Throwable $e) {}
    log_admin_action('admin_login', 'Successful admin login.');
    return true;
}

function admin_logout(): void {
    unset($_SESSION['admin_user_id'], $_SESSION['admin_name'], $_SESSION['admin_username'], $_SESSION['admin_avatar'], $_SESSION['admin_last_seen'], $_SESSION['admin_fingerprint']);
    session_regenerate_id(true);
}
