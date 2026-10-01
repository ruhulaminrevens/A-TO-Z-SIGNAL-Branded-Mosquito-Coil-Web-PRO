<?php
require_once __DIR__ . '/_layout.php';
require_admin();
ensure_phase2_cms_schema();
$pdo = db();
$id=(int)($_GET['id'] ?? 0);
$member=null;
if($id){ $stmt=$pdo->prepare('SELECT * FROM site_team WHERE id=?'); $stmt->execute([$id]); $member=$stmt->fetch(); }
$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
  try{
    verify_csrf();
    $nameEn=trim((string)($_POST['name_en'] ?? ''));
    if($nameEn==='') throw new RuntimeException('English name is required.');
    $imagePath=$member['image'] ?? '';
    $uploaded=upload_image_file('image','uploads/team',slugify($nameEn),3*1024*1024);
    if($uploaded) $imagePath=$uploaded;
    $data=[$nameEn,trim((string)($_POST['name_bn'] ?? '')),trim((string)($_POST['role_en'] ?? '')),trim((string)($_POST['role_bn'] ?? '')),trim((string)($_POST['intro_en'] ?? '')),trim((string)($_POST['intro_bn'] ?? '')),$imagePath,(int)($_POST['sort_order'] ?? 0),isset($_POST['is_active'])?1:0];
    if($id){ $data[]=$id; $stmt=$pdo->prepare('UPDATE site_team SET name_en=?, name_bn=?, role_en=?, role_bn=?, intro_en=?, intro_bn=?, image=?, sort_order=?, is_active=?, updated_at=NOW() WHERE id=?'); $stmt->execute($data); }
    else { $stmt=$pdo->prepare('INSERT INTO site_team (name_en,name_bn,role_en,role_bn,intro_en,intro_bn,image,sort_order,is_active,created_at,updated_at) VALUES (?,?,?,?,?,?,?,?,?,NOW(),NOW())'); $stmt->execute($data); }
    log_admin_action('team_member_save', 'Leadership team member saved.');
    header('Location: team.php'); exit;
  }catch(Throwable $e){ $error=$e->getMessage(); }
}
admin_header($id ? 'Edit Team Member' : 'Add Team Member');
?>
<?php if($error): ?><div class="alert"><?= e($error) ?></div><?php endif; ?>
<form class="panel form-grid enhanced-form-v30" method="post" enctype="multipart/form-data"><?= csrf_field() ?>
  <div class="form-intro-v30 full"><span>Leadership CMS</span><p>Use consistent portrait photos and balanced Bangla/English descriptions for professional About Us cards.</p></div>
  <label>Name English<input name="name_en" value="<?= e($member['name_en'] ?? '') ?>" required></label>
  <label>Name Bangla<input name="name_bn" value="<?= e($member['name_bn'] ?? '') ?>"></label>
  <label>Role English<input name="role_en" value="<?= e($member['role_en'] ?? '') ?>"></label>
  <label>Role Bangla<input name="role_bn" value="<?= e($member['role_bn'] ?? '') ?>"></label>
  <label class="full">Intro English<textarea name="intro_en" rows="4"><?= e($member['intro_en'] ?? '') ?></textarea></label>
  <label class="full">Intro Bangla<textarea name="intro_bn" rows="4"><?= e($member['intro_bn'] ?? '') ?></textarea></label>
  <label>Sort Order<input type="number" name="sort_order" value="<?= e((string)($member['sort_order'] ?? 0)) ?>"></label>
  <label>Portrait Photo<input type="file" name="image" accept=".jpg,.jpeg,.png,.webp" data-image-preview="#teamImagePreview"><small>JPG, PNG or WEBP. Maximum 3MB.</small></label>
  <label class="check"><input type="checkbox" name="is_active" <?= ($member['is_active'] ?? 1) ? 'checked' : '' ?>> Active / visible</label>
  <div class="image-preview-box-v30"><img id="teamImagePreview" class="preview" src="<?= !empty($member['image']) ? '../'.e($member['image']) : '' ?>" alt="" <?= empty($member['image']) ? 'hidden' : '' ?>><small>Live photo preview</small></div>
  <div class="full form-submit-row-v30"><button class="button" type="submit">Save Team Member</button><a class="button secondary" href="team.php">Cancel</a></div>
</form>
<?php admin_footer(); ?>
