<?php
require_once __DIR__ . '/_layout.php';
require_admin();
ensure_admin_profile_columns();
$pdo = db();
$admin = admin_fetch_current();
if (!$admin) { admin_logout(); header('Location: login.php'); exit; }

$error = '';
$msg = '';

function admin_profile_upload_avatar(array $file, int $adminId): string {
    if (empty($file['name'])) return '';
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) throw new RuntimeException('Profile image upload failed.');
    if (($file['size'] ?? 0) > 2 * 1024 * 1024) throw new RuntimeException('Profile image must be 2MB or less.');
    $info = @getimagesize($file['tmp_name']);
    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $mime = $info['mime'] ?? '';
    if (!isset($allowed[$mime])) throw new RuntimeException('Only JPG, PNG or WEBP profile images are allowed.');
    $dir = __DIR__ . '/../uploads/admin';
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    $htaccess = $dir . '/.htaccess';
    if (!file_exists($htaccess)) {
        file_put_contents($htaccess, "Options -Indexes\n<FilesMatch \"\\.(php|phtml|php5|phar)$\">\n    Require all denied\n</FilesMatch>\n");
    }
    $fileName = 'admin-profile-' . $adminId . '-' . time() . '.' . $allowed[$mime];
    $dest = $dir . '/' . $fileName;
    if (!move_uploaded_file($file['tmp_name'], $dest)) throw new RuntimeException('Could not save profile image.');
    return 'uploads/admin/' . $fileName;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        verify_csrf();
        $name = trim($_POST['name'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        if ($name === '') throw new RuntimeException('Display name is required.');
        if ($username === '') throw new RuntimeException('Username is required.');
        if (!preg_match('/^[a-zA-Z0-9_.-]{3,60}$/', $username)) throw new RuntimeException('Username must be 3-60 characters and use only letters, numbers, dot, dash or underscore.');

        $changingSensitive = $username !== ($admin['username'] ?? '') || $newPassword !== '';
        if ($changingSensitive && !password_verify($currentPassword, $admin['password_hash'])) {
            throw new RuntimeException('Current password is required to change username or password.');
        }
        if ($newPassword !== '') {
            if (!password_is_strong($newPassword)) throw new RuntimeException('New password must be at least 10 characters and include uppercase, lowercase, number and symbol.');
            if ($newPassword !== $confirmPassword) throw new RuntimeException('New password and confirmation do not match.');
        }
        if ($username !== ($admin['username'] ?? '')) {
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM admin_users WHERE username = ? AND id <> ?');
            $stmt->execute([$username, (int)$admin['id']]);
            if ((int)$stmt->fetchColumn() > 0) throw new RuntimeException('This username is already taken.');
        }

        $avatar = $admin['avatar'] ?? '';
        $uploadedAvatar = admin_profile_upload_avatar($_FILES['avatar'] ?? [], (int)$admin['id']);
        if ($uploadedAvatar !== '') $avatar = $uploadedAvatar;

        if ($newPassword !== '') {
            $stmt = $pdo->prepare('UPDATE admin_users SET username=?, name=?, avatar=?, password_hash=?, password_updated_at=NOW(), updated_at=NOW() WHERE id=?');
            $stmt->execute([$username, $name, $avatar, password_hash($newPassword, PASSWORD_DEFAULT), (int)$admin['id']]);
        } else {
            $stmt = $pdo->prepare('UPDATE admin_users SET username=?, name=?, avatar=?, updated_at=NOW() WHERE id=?');
            $stmt->execute([$username, $name, $avatar, (int)$admin['id']]);
        }

        $admin = admin_fetch_current() ?: $admin;
        admin_refresh_session($admin);
        log_admin_action('profile_update', 'Admin profile information was updated.');
        $msg = 'Profile updated successfully.';
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

admin_header('Admin Profile');
?>
<?php if($msg): ?><div class="success"><?= e($msg) ?></div><?php endif; ?>
<?php if($error): ?><div class="alert"><?= e($error) ?></div><?php endif; ?>
<section class="profile-layout-v28">
    <aside class="panel profile-card-v28">
        <div class="profile-avatar-big-v28">
            <?php if(!empty($admin['avatar'])): ?><img src="../<?= e($admin['avatar']) ?>" alt="<?= e($admin['name'] ?: $admin['username']) ?>"><?php else: ?><span><?= e(admin_initials($admin['name'] ?: $admin['username'])) ?></span><?php endif; ?>
        </div>
        <h2><?= e($admin['name'] ?: $admin['username']) ?></h2>
        <p>@<?= e($admin['username']) ?></p>
        <div class="profile-meta-v28">
            <span><b>Last login</b><?= e((string)($admin['last_login_at'] ?? 'Not recorded')) ?></span>
            <span><b>Password updated</b><?= e((string)($admin['password_updated_at'] ?? 'Not recorded')) ?></span>
        </div>
    </aside>

    <form class="panel form-grid profile-form-v28" method="post" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <div class="full form-section-title-v28"><span>Profile Information</span><p>Update admin photo, display name and username.</p></div>
        <label>Display Name<input name="name" value="<?= e($admin['name'] ?? '') ?>" required></label>
        <label>Username<input name="username" value="<?= e($admin['username'] ?? '') ?>" required autocomplete="username"><small>Letters, numbers, dot, dash or underscore only.</small></label>
        <label class="full">Profile Photo<input type="file" name="avatar" accept=".jpg,.jpeg,.png,.webp" data-image-preview="#profilePreview"><small>JPG, PNG or WEBP. Maximum 2MB.</small></label>
        <div class="full profile-preview-row-v28"><img id="profilePreview" src="<?= !empty($admin['avatar']) ? '../' . e($admin['avatar']) : '' ?>" alt="Profile preview" <?= empty($admin['avatar']) ? 'hidden' : '' ?>></div>

        <div class="full form-section-title-v28"><span>Password & Security</span><p>Current password is required only when changing username or password.</p></div>
        <label class="full">Current Password<input type="password" name="current_password" autocomplete="current-password" data-password-field></label>
        <label>New Password<input type="password" name="new_password" autocomplete="new-password" data-password-field><small>Leave blank to keep current password. Use 10+ chars with uppercase, lowercase, number and symbol.</small></label>
        <label>Confirm New Password<input type="password" name="confirm_password" autocomplete="new-password" data-password-field></label>
        <div class="full profile-actions-v28"><button class="button" type="submit">Save Profile</button><button class="button secondary" type="button" data-toggle-passwords>Show / Hide Passwords</button></div>
    </form>
</section>
<?php admin_footer(); ?>
