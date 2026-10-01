<?php
require_once __DIR__ . '/_layout.php';
require_admin();
ensure_phase2_cms_schema();
$pdo = db();
$id=(int)($_GET['id'] ?? 0);
$entry=null;
if($id){ $stmt=$pdo->prepare('SELECT * FROM company_timeline WHERE id=?'); $stmt->execute([$id]); $entry=$stmt->fetch(); }
$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
  try{
    verify_csrf();
    $titleEn=trim((string)($_POST['title_en'] ?? ''));
    $year=trim((string)($_POST['year_label'] ?? ''));
    if($year==='') throw new RuntimeException('Year/label is required.');
    if($titleEn==='') throw new RuntimeException('English title is required.');
    $imagePath=$entry['image'] ?? '';
    $uploaded=upload_image_file('image','uploads/blog',slugify($year.'-'.$titleEn),5*1024*1024);
    if($uploaded) $imagePath=$uploaded;
    $data=[$year,$titleEn,trim((string)($_POST['title_bn'] ?? '')),trim((string)($_POST['excerpt_en'] ?? '')),trim((string)($_POST['excerpt_bn'] ?? '')),$imagePath,(int)($_POST['sort_order'] ?? 0),isset($_POST['is_active'])?1:0];
    if($id){ $data[]=$id; $stmt=$pdo->prepare('UPDATE company_timeline SET year_label=?, title_en=?, title_bn=?, excerpt_en=?, excerpt_bn=?, image=?, sort_order=?, is_active=?, updated_at=NOW() WHERE id=?'); $stmt->execute($data); }
    else { $stmt=$pdo->prepare('INSERT INTO company_timeline (year_label,title_en,title_bn,excerpt_en,excerpt_bn,image,sort_order,is_active,created_at,updated_at) VALUES (?,?,?,?,?,?,?,?,NOW(),NOW())'); $stmt->execute($data); }
    log_admin_action('timeline_save', 'Company timeline entry saved.');
    header('Location: timeline.php'); exit;
  }catch(Throwable $e){ $error=$e->getMessage(); }
}
admin_header($id ? 'Edit Milestone' : 'Add Milestone');
?>
<?php if($error): ?><div class="alert"><?= e($error) ?></div><?php endif; ?>
<form class="panel form-grid enhanced-form-v30" method="post" enctype="multipart/form-data"><?= csrf_field() ?>
  <div class="form-intro-v30 full"><span>Timeline CMS</span><p>Add sales meets, launches, market activities and company milestones for the About Us archive section.</p></div>
  <label>Year / Label<input name="year_label" value="<?= e($entry['year_label'] ?? '') ?>" placeholder="2025 / Team" required></label>
  <label>Title English<input name="title_en" value="<?= e($entry['title_en'] ?? '') ?>" required></label>
  <label class="full">Title Bangla<input name="title_bn" value="<?= e($entry['title_bn'] ?? '') ?>"></label>
  <label class="full">Excerpt English<textarea name="excerpt_en" rows="3"><?= e($entry['excerpt_en'] ?? '') ?></textarea></label>
  <label class="full">Excerpt Bangla<textarea name="excerpt_bn" rows="3"><?= e($entry['excerpt_bn'] ?? '') ?></textarea></label>
  <label>Sort Order<input type="number" name="sort_order" value="<?= e((string)($entry['sort_order'] ?? 0)) ?>"></label>
  <label>Milestone Image<input type="file" name="image" accept=".jpg,.jpeg,.png,.webp" data-image-preview="#timelineImagePreview"><small>JPG, PNG or WEBP. Maximum 5MB.</small></label>
  <label class="check"><input type="checkbox" name="is_active" <?= ($entry['is_active'] ?? 1) ? 'checked' : '' ?>> Active / visible</label>
  <div class="image-preview-box-v30"><img id="timelineImagePreview" class="preview" src="<?= !empty($entry['image']) ? '../'.e($entry['image']) : '' ?>" alt="" <?= empty($entry['image']) ? 'hidden' : '' ?>><small>Live image preview</small></div>
  <div class="full form-submit-row-v30"><button class="button" type="submit">Save Milestone</button><a class="button secondary" href="timeline.php">Cancel</a></div>
</form>
<?php admin_footer(); ?>
