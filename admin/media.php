<?php
require_once __DIR__ . '/_layout.php';
require_admin();
ensure_phase43_media_content_schema();
$pdo = db();
$error = '';
$flash = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        verify_csrf();
        $action = (string)($_POST['action'] ?? 'upload');
        if ($action === 'upload') {
            $title = trim((string)($_POST['title'] ?? ''));
            $alt = trim((string)($_POST['alt_text'] ?? ''));
            $caption = trim((string)($_POST['caption'] ?? ''));
            $path = upload_image_file('media_file', 'uploads/media', $title ?: 'media-asset', 8 * 1024 * 1024);
            if (!$path) throw new RuntimeException('Please choose an image file to upload.');
            register_media_asset([
                'file_path' => $path,
                'title' => $title ?: pathinfo($path, PATHINFO_FILENAME),
                'alt_text' => $alt ?: $title,
                'caption' => $caption,
                'source' => 'media-library',
                'usage_context' => 'uploads/media',
            ]);
            log_admin_action('media_upload', 'Uploaded media asset: ' . $path);
            $flash = 'Media uploaded and optimized successfully.';
        } elseif ($action === 'sync') {
            $count = scan_uploads_into_media_library();
            log_admin_action('media_sync', 'Synced uploads into media library. Count: ' . $count);
            $flash = $count . ' file(s) scanned into the media library.';
        } elseif ($action === 'update') {
            $id = (int)($_POST['id'] ?? 0);
            $stmt = $pdo->prepare('UPDATE media_assets SET title=?, alt_text=?, caption=?, usage_context=?, updated_at=NOW() WHERE id=?');
            $stmt->execute([
                trim((string)($_POST['title'] ?? '')),
                trim((string)($_POST['alt_text'] ?? '')),
                trim((string)($_POST['caption'] ?? '')),
                trim((string)($_POST['usage_context'] ?? '')),
                $id,
            ]);
            log_admin_action('media_update', 'Updated media asset ID ' . $id);
            $flash = 'Media details updated.';
        } elseif ($action === 'delete') {
            $id = (int)($_POST['id'] ?? 0);
            $stmt = $pdo->prepare('SELECT * FROM media_assets WHERE id=? LIMIT 1');
            $stmt->execute([$id]);
            $asset = $stmt->fetch();
            if (!$asset) throw new RuntimeException('Media asset not found.');
            safe_delete_upload_file((string)$asset['file_path']);
            if (!empty($asset['original_path']) && $asset['original_path'] !== $asset['file_path']) safe_delete_upload_file((string)$asset['original_path']);
            $pdo->prepare('DELETE FROM media_assets WHERE id=?')->execute([$id]);
            log_admin_action('media_delete', 'Deleted media asset ID ' . $id);
            $flash = 'Media asset deleted from the library.';
        }
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

$q = trim((string)($_GET['q'] ?? ''));
$type = trim((string)($_GET['type'] ?? ''));
$assets = get_media_assets(['q' => $q, 'type' => $type], 120);
$totalAssets = (int)$pdo->query('SELECT COUNT(*) FROM media_assets')->fetchColumn();
$imageAssets = (int)$pdo->query("SELECT COUNT(*) FROM media_assets WHERE mime_type LIKE 'image/%'")->fetchColumn();
$totalSize = (int)$pdo->query('SELECT COALESCE(SUM(file_size),0) FROM media_assets')->fetchColumn();
admin_header('Media Library');
?>
<?php if($flash): ?><div class="success"><?= e($flash) ?></div><?php endif; ?>
<?php if($error): ?><div class="alert"><?= e($error) ?></div><?php endif; ?>

<section class="panel admin-list-head-v30 media-head-v43">
    <div>
        <span class="panel-eyebrow">v43 Media System</span>
        <h2>Upload, optimize, reuse and copy media paths from one library.</h2>
        <p><?= $totalAssets ?> assets · <?= $imageAssets ?> image assets · <?= number_format($totalSize / 1024 / 1024, 2) ?> MB indexed. New uploads create WebP variants automatically when supported.</p>
    </div>
    <div class="admin-list-actions-v30">
        <form method="post" class="inline-admin-form-v43"><?= csrf_field() ?><input type="hidden" name="action" value="sync"><button class="button secondary" type="submit">Sync Uploads</button></form>
        <a class="button ghost" href="content-blocks.php">Content Blocks</a>
    </div>
</section>

<section class="panel media-upload-panel-v43">
    <form method="post" enctype="multipart/form-data" class="form-grid enhanced-form-v30">
        <?= csrf_field() ?><input type="hidden" name="action" value="upload">
        <div class="form-intro-v30 full"><span>Upload New Media</span><p>Use clean JPG/PNG/WEBP images. The system stores optimized WebP where possible and indexes metadata for reuse.</p></div>
        <label>Title<input name="title" placeholder="Product pack, event photo, banner..."></label>
        <label>Alt Text<input name="alt_text" placeholder="Short accessible image description"></label>
        <label class="full">Caption<textarea name="caption" rows="2" placeholder="Optional internal note or caption"></textarea></label>
        <label class="full">Choose Image<input type="file" name="media_file" accept=".jpg,.jpeg,.png,.webp" data-image-preview="#mediaUploadPreview"><small>Maximum 8MB. WebP conversion depends on hosting GD/WebP support.</small></label>
        <div class="image-preview-box-v30"><img id="mediaUploadPreview" class="preview" src="" alt="" hidden><small>Live preview</small></div>
        <div class="full form-submit-row-v30"><button class="button" type="submit">Upload Media</button></div>
    </form>
</section>

<section class="panel admin-filter-panel-v30">
    <form class="admin-filters-v30" method="get">
        <label>Search<input name="q" value="<?= e($q) ?>" placeholder="Title, path, caption..."></label>
        <label>Type<select name="type"><option value="">All types</option><option value="images" <?= $type==='images'?'selected':'' ?>>Images</option><option value="documents" <?= $type==='documents'?'selected':'' ?>>Documents/Other</option></select></label>
        <div class="filter-actions-v30"><button class="button" type="submit">Filter</button><a class="button ghost" href="media.php">Reset</a></div>
    </form>
</section>

<section class="media-grid-v43">
    <?php foreach($assets as $asset): $isImage = str_starts_with((string)$asset['mime_type'], 'image/'); ?>
        <article class="panel media-card-v43">
            <div class="media-thumb-v43">
                <?php if($isImage): ?><img src="../<?= e($asset['file_path']) ?>" alt="<?= e($asset['alt_text'] ?: $asset['title']) ?>" loading="lazy"><?php else: ?><span><?= e(strtoupper($asset['file_ext'] ?: 'FILE')) ?></span><?php endif; ?>
            </div>
            <form method="post" class="media-card-body-v43">
                <?= csrf_field() ?><input type="hidden" name="action" value="update"><input type="hidden" name="id" value="<?= (int)$asset['id'] ?>">
                <label>Title<input name="title" value="<?= e($asset['title'] ?? '') ?>"></label>
                <label>Alt Text<input name="alt_text" value="<?= e($asset['alt_text'] ?? '') ?>"></label>
                <label>Usage<input name="usage_context" value="<?= e($asset['usage_context'] ?? '') ?>"></label>
                <label>Caption<textarea name="caption" rows="2"><?= e($asset['caption'] ?? '') ?></textarea></label>
                <div class="media-path-copy-v43"><code><?= e($asset['file_path']) ?></code><button type="button" class="mini-button neutral" data-copy-text="<?= e($asset['file_path']) ?>">Copy</button></div>
                <small><?= e($asset['mime_type'] ?: 'unknown') ?> · <?= number_format(((int)$asset['file_size']) / 1024, 1) ?> KB<?php if(!empty($asset['width'])): ?> · <?= (int)$asset['width'] ?>×<?= (int)$asset['height'] ?><?php endif; ?></small>
                <div class="row-actions-v30"><button class="mini-button" type="submit">Save</button><a class="mini-button neutral" href="../<?= e($asset['file_path']) ?>" target="_blank">Open</a></div>
            </form>
            <form method="post" class="media-delete-form-v43" data-confirm-form="Delete this media asset from the library and uploads folder?"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$asset['id'] ?>"><button class="danger" type="submit">Delete</button></form>
        </article>
    <?php endforeach; ?>
    <?php if(!$assets): ?><div class="panel"><p>No media assets found. Upload a file or click Sync Uploads.</p></div><?php endif; ?>
</section>
<?php admin_footer(); ?>
