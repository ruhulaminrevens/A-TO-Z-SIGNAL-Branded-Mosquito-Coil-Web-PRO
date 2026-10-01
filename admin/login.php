<?php
require_once __DIR__ . '/../includes/auth.php';
apply_security_headers();
$error = '';
if (isset($_GET['timeout'])) $error = 'Session expired for security. Please login again.';
if (isset($_GET['security'])) $error = 'Security check required a fresh login.';
if (is_admin_logged_in()) { header('Location: index.php'); exit; }
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        verify_csrf();
        if (admin_login(trim($_POST['username'] ?? ''), $_POST['password'] ?? '')) { header('Location: index.php'); exit; }
        $error = 'Invalid username or password.';
    } catch (Throwable $e) { $error = $e->getMessage(); }
}
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Admin Login</title><link rel="stylesheet" href="../assets/css/admin.css?v=<?= e(app_asset_version()) ?>"></head><body class="login-body"><form class="login-card" method="post"><?= csrf_field() ?><div class="login-brand-logos"><img src="../assets/img/logo-atoz.webp" alt="A TO Z" width="1598" height="1041"><img src="../assets/img/logo-signal.webp" alt="SIGNAL" width="2047" height="880"></div><h1>Admin Login</h1><p>Premium Control Panel</p><?php if($error): ?><div class="alert"><?= e($error) ?></div><?php endif; ?><label>Username<input name="username" required autofocus autocomplete="username"></label><label>Password<input type="password" name="password" required autocomplete="current-password"></label><button type="submit">Login</button><small>Security: 5 failed attempts will lock login for 15 minutes.</small></form></body></html>
