<?php
require_once __DIR__ . '/_layout.php';
require_admin();
ensure_phase2_cms_schema();
$pdo = db();
$flash=''; $error='';
if (isset($_GET['toggle'], $_GET['token']) && hash_equals($_SESSION['csrf_token'] ?? '', (string)$_GET['token'])) {
    try { $stmt=$pdo->prepare('UPDATE site_team SET is_active=IF(is_active=1,0,1), updated_at=NOW() WHERE id=?'); $stmt->execute([(int)$_GET['toggle']]); header('Location: team.php'); exit; } catch(Throwable $e){ $error=$e->getMessage(); }
}
if ($_SERVER['REQUEST_METHOD']==='POST') {
    try { verify_csrf(); $orders=$_POST['sort_order'] ?? []; if(is_array($orders)){ $stmt=$pdo->prepare('UPDATE site_team SET sort_order=?, updated_at=NOW() WHERE id=?'); foreach($orders as $id=>$order){ $stmt->execute([(int)$order,(int)$id]); }} $flash='Team order saved.'; } catch(Throwable $e){ $error=$e->getMessage(); }
}
$rows = get_team_members(false);
$active = count(array_filter($rows, static fn($r)=> (int)($r['is_active'] ?? 1) === 1));
admin_header('Leadership Team');
?>
<?php if($flash): ?><div class="success"><?= e($flash) ?></div><?php endif; ?>
<?php if($error): ?><div class="alert"><?= e($error) ?></div><?php endif; ?>
<section class="panel admin-list-head-v30">
  <div><span class="panel-eyebrow">Phase 2 CMS</span><h2>Manage About Us leadership cards.</h2><p><?= $active ?> active out of <?= count($rows) ?> team member(s). Update photos, designations, Bangla/English copy and display order.</p></div>
  <div class="admin-list-actions-v30"><a class="button" href="team-form.php">+ Add Team Member</a><a class="button secondary" href="<?= e(page_url('about')) ?>" target="_blank" rel="noopener">View About Us</a></div>
</section>
<form class="panel table-wrap admin-table-panel-v30" method="post"><?= csrf_field() ?>
  <div class="bulk-toolbar-v30"><strong><?= count($rows) ?> member(s)</strong><button class="button secondary" type="submit">Save Sort Order</button></div>
  <table class="admin-data-table-v30">
    <thead><tr><th>Photo</th><th>Name</th><th>Role</th><th>Order</th><th>Status</th><th>Actions</th></tr></thead>
    <tbody>
    <?php foreach($rows as $r): ?>
      <tr>
        <td data-label="Photo"><?php if(!empty($r['image'])): ?><img class="thumb" src="../<?= e($r['image']) ?>" alt=""><?php endif; ?></td>
        <td data-label="Name"><strong><?= e($r['name_en']) ?></strong><br><small><?= e($r['name_bn'] ?? '') ?></small></td>
        <td data-label="Role"><?= e($r['role_en'] ?? '') ?><br><small><?= e($r['role_bn'] ?? '') ?></small></td>
        <td data-label="Order"><input class="order-input-v30" type="number" name="sort_order[<?= (int)$r['id'] ?>]" value="<?= (int)($r['sort_order'] ?? 0) ?>"></td>
        <td data-label="Status"><span class="status-pill-v30 <?= !empty($r['is_active']) ? 'active' : 'hidden' ?>"><?= !empty($r['is_active']) ? 'Active' : 'Hidden' ?></span></td>
        <td data-label="Actions" class="row-actions-v30"><a class="mini-button" href="team-form.php?id=<?= (int)$r['id'] ?>">Edit</a><a class="mini-button neutral" href="team.php?toggle=<?= (int)$r['id'] ?>&token=<?= e(csrf_token()) ?>"><?= !empty($r['is_active']) ? 'Hide' : 'Show' ?></a><a class="danger" data-confirm="Delete this team member?" href="team-delete.php?id=<?= (int)$r['id'] ?>&token=<?= e(csrf_token()) ?>">Delete</a></td>
      </tr>
    <?php endforeach; ?>
    <?php if(!$rows): ?><tr><td colspan="6">No team members found.</td></tr><?php endif; ?>
    </tbody>
  </table>
</form>
<?php admin_footer(); ?>
