<?php
require_once __DIR__ . '/_layout.php';
require_admin();
ensure_phase2_cms_schema();
$pdo = db();
$flash=''; $error='';
if (isset($_GET['toggle'], $_GET['token']) && hash_equals($_SESSION['csrf_token'] ?? '', (string)$_GET['token'])) {
    try { $stmt=$pdo->prepare('UPDATE company_timeline SET is_active=IF(is_active=1,0,1), updated_at=NOW() WHERE id=?'); $stmt->execute([(int)$_GET['toggle']]); header('Location: timeline.php'); exit; } catch(Throwable $e){ $error=$e->getMessage(); }
}
if ($_SERVER['REQUEST_METHOD']==='POST') {
    try { verify_csrf(); $orders=$_POST['sort_order'] ?? []; if(is_array($orders)){ $stmt=$pdo->prepare('UPDATE company_timeline SET sort_order=?, updated_at=NOW() WHERE id=?'); foreach($orders as $id=>$order){ $stmt->execute([(int)$order,(int)$id]); }} $flash='Timeline order saved.'; } catch(Throwable $e){ $error=$e->getMessage(); }
}
$rows = get_timeline_entries(false);
$active = count(array_filter($rows, static fn($r)=> (int)($r['is_active'] ?? 1) === 1));
admin_header('Company Timeline');
?>
<?php if($flash): ?><div class="success"><?= e($flash) ?></div><?php endif; ?>
<?php if($error): ?><div class="alert"><?= e($error) ?></div><?php endif; ?>
<section class="panel admin-list-head-v30">
  <div><span class="panel-eyebrow">Phase 2 CMS</span><h2>Manage company archive and About Us timeline.</h2><p><?= $active ?> active out of <?= count($rows) ?> milestone(s). These entries power the About Us gallery and business story.</p></div>
  <div class="admin-list-actions-v30"><a class="button" href="timeline-form.php">+ Add Milestone</a><a class="button secondary" href="<?= e(page_url('about')) ?>" target="_blank" rel="noopener">View About Us</a></div>
</section>
<form class="panel table-wrap admin-table-panel-v30" method="post"><?= csrf_field() ?>
  <div class="bulk-toolbar-v30"><strong><?= count($rows) ?> milestone(s)</strong><button class="button secondary" type="submit">Save Sort Order</button></div>
  <table class="admin-data-table-v30">
    <thead><tr><th>Image</th><th>Year</th><th>Title</th><th>Order</th><th>Status</th><th>Actions</th></tr></thead>
    <tbody>
    <?php foreach($rows as $r): ?>
      <tr>
        <td data-label="Image"><?php if(!empty($r['image'])): ?><img class="mini-thumb" src="../<?= e($r['image']) ?>" alt=""><?php endif; ?></td>
        <td data-label="Year"><strong><?= e($r['year_label']) ?></strong></td>
        <td data-label="Title"><strong><?= e($r['title_en']) ?></strong><br><small><?= e($r['title_bn'] ?? '') ?></small></td>
        <td data-label="Order"><input class="order-input-v30" type="number" name="sort_order[<?= (int)$r['id'] ?>]" value="<?= (int)($r['sort_order'] ?? 0) ?>"></td>
        <td data-label="Status"><span class="status-pill-v30 <?= !empty($r['is_active']) ? 'active' : 'hidden' ?>"><?= !empty($r['is_active']) ? 'Active' : 'Hidden' ?></span></td>
        <td data-label="Actions" class="row-actions-v30"><a class="mini-button" href="timeline-form.php?id=<?= (int)$r['id'] ?>">Edit</a><a class="mini-button neutral" href="timeline.php?toggle=<?= (int)$r['id'] ?>&token=<?= e(csrf_token()) ?>"><?= !empty($r['is_active']) ? 'Hide' : 'Show' ?></a><a class="danger" data-confirm="Delete this milestone?" href="timeline-delete.php?id=<?= (int)$r['id'] ?>&token=<?= e(csrf_token()) ?>">Delete</a></td>
      </tr>
    <?php endforeach; ?>
    <?php if(!$rows): ?><tr><td colspan="6">No timeline milestones found.</td></tr><?php endif; ?>
    </tbody>
  </table>
</form>
<?php admin_footer(); ?>
