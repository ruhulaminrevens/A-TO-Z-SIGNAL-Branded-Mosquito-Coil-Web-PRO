<?php
require_once __DIR__ . '/_layout.php';
require_admin();
ensure_phase6_security_schema();
$pdo = db();
$error = '';
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        verify_csrf();
        $action = $_POST['action'] ?? '';
        if ($action === 'export_backup') {
            $backup = build_operational_backup();
            log_admin_action('maintenance_backup', 'Operational JSON backup exported.');
            header('Content-Type: application/json; charset=utf-8');
            header('Content-Disposition: attachment; filename=atoz-signal-operational-backup-' . date('Y-m-d-His') . '.json');
            echo json_encode($backup, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        }
        if ($action === 'clear_old_logs') {
            $pdo->exec("DELETE FROM admin_activity_log WHERE created_at < (NOW() - INTERVAL 90 DAY)");
            $pdo->exec("DELETE FROM admin_login_attempts WHERE attempted_at < (NOW() - INTERVAL 30 DAY)");
            log_admin_action('maintenance_cleanup', 'Old activity and login attempt logs were cleaned.');
            $msg = 'Old logs cleaned successfully.';
        }
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

$checks = maintenance_system_checks();
$okCount = count(array_filter($checks, static fn($item) => !empty($item['ok'])));
$totalChecks = count($checks);
$activity = [];
$attempts = [];
try {
    $activity = $pdo->query('SELECT * FROM admin_activity_log ORDER BY id DESC LIMIT 20')->fetchAll();
    $attempts = $pdo->query('SELECT username, ip_address, attempted_at, was_successful FROM admin_login_attempts ORDER BY id DESC LIMIT 10')->fetchAll();
} catch (Throwable $e) {}

admin_header('Security & Maintenance');
?>
<?php if($msg): ?><div class="success"><?= e($msg) ?></div><?php endif; ?>
<?php if($error): ?><div class="alert"><?= e($error) ?></div><?php endif; ?>

<section class="panel admin-list-head-v30 phase6-hero-v37">
    <div>
        <span class="panel-eyebrow">Phase 6 Control</span>
        <h2>Security, maintenance and operational health center.</h2>
        <p><?= $okCount ?> of <?= $totalChecks ?> hosting checks are passing. Review login security, backups, server readiness and admin activity from one place.</p>
    </div>
    <div class="phase6-score-v37"><strong><?= round(($okCount / max(1, $totalChecks)) * 100) ?>%</strong><span>System readiness</span></div>
</section>

<section class="phase6-grid-v37">
    <article class="panel phase6-card-v37">
        <h3>System Health</h3>
        <div class="phase6-checklist-v37">
            <?php foreach($checks as $check): ?>
                <div class="<?= !empty($check['ok']) ? 'ok' : 'warn' ?>"><span><?= !empty($check['ok']) ? '✓' : '!' ?></span><b><?= e($check['label']) ?></b><em><?= e($check['value']) ?></em></div>
            <?php endforeach; ?>
        </div>
    </article>

    <article class="panel phase6-card-v37">
        <h3>Backup & Cleanup</h3>
        <p>Export a JSON backup of key business tables before big edits or hosting migration.</p>
        <form method="post" class="phase6-actions-v37"><?= csrf_field() ?><input type="hidden" name="action" value="export_backup"><button class="button" type="submit">Download Backup JSON</button></form>
        <form method="post" class="phase6-actions-v37" data-confirm-form="Clean old logs?"><?= csrf_field() ?><input type="hidden" name="action" value="clear_old_logs"><button class="button secondary" type="submit">Clean Old Logs</button></form>
        <small>Backup includes settings, products, blogs, enquiries, team, timeline and activity logs.</small>
    </article>
</section>

<section class="panel table-wrap admin-table-panel-v30">
    <div class="bulk-toolbar-v30"><strong>Recent Admin Activity</strong><span>Audit log for important admin actions.</span></div>
    <table class="admin-data-table-v30">
        <thead><tr><th>Time</th><th>Admin</th><th>Action</th><th>Details</th><th>IP</th></tr></thead>
        <tbody>
        <?php foreach($activity as $row): ?>
            <tr><td data-label="Time"><?= e((string)$row['created_at']) ?></td><td data-label="Admin"><?= e((string)$row['admin_name']) ?></td><td data-label="Action"><span class="status-pill-v30 active"><?= e((string)$row['action']) ?></span></td><td data-label="Details"><?= e((string)$row['details']) ?></td><td data-label="IP"><?= e((string)$row['ip_address']) ?></td></tr>
        <?php endforeach; ?>
        <?php if(!$activity): ?><tr><td colspan="5">No activity recorded yet.</td></tr><?php endif; ?>
        </tbody>
    </table>
</section>

<section class="panel table-wrap admin-table-panel-v30">
    <div class="bulk-toolbar-v30"><strong>Recent Login Attempts</strong><span>Failed attempts are automatically limited.</span></div>
    <table class="admin-data-table-v30">
        <thead><tr><th>Time</th><th>Username</th><th>IP</th><th>Status</th></tr></thead>
        <tbody>
        <?php foreach($attempts as $row): ?>
            <tr><td data-label="Time"><?= e((string)$row['attempted_at']) ?></td><td data-label="Username"><?= e((string)$row['username']) ?></td><td data-label="IP"><?= e((string)$row['ip_address']) ?></td><td data-label="Status"><span class="status-pill-v30 <?= !empty($row['was_successful']) ? 'active' : 'hidden' ?>"><?= !empty($row['was_successful']) ? 'Success' : 'Failed' ?></span></td></tr>
        <?php endforeach; ?>
        <?php if(!$attempts): ?><tr><td colspan="4">No login attempt data yet.</td></tr><?php endif; ?>
        </tbody>
    </table>
</section>
<?php admin_footer(); ?>
