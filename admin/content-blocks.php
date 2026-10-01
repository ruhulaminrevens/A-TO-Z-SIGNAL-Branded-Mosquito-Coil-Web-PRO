<?php
require_once __DIR__ . '/_layout.php';
require_admin();
ensure_phase43_media_content_schema();
$pdo = db();
$error = '';
$flash = '';
$id = (int)($_GET['id'] ?? 0);
$block = null;
if ($id) {
    $stmt = $pdo->prepare('SELECT * FROM content_blocks WHERE id=? LIMIT 1');
    $stmt->execute([$id]);
    $block = $stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        verify_csrf();
        $action = (string)($_POST['action'] ?? 'save');
        if ($action === 'toggle') {
            $toggleId = (int)($_POST['id'] ?? 0);
            $pdo->prepare('UPDATE content_blocks SET is_active = IF(is_active=1,0,1), updated_at=NOW() WHERE id=?')->execute([$toggleId]);
            log_admin_action('content_block_toggle', 'Toggled content block ID ' . $toggleId);
            header('Location: content-blocks.php'); exit;
        }
        if ($action === 'delete') {
            $deleteId = (int)($_POST['id'] ?? 0);
            $pdo->prepare('DELETE FROM content_blocks WHERE id=?')->execute([$deleteId]);
            log_admin_action('content_block_delete', 'Deleted content block ID ' . $deleteId);
            header('Location: content-blocks.php'); exit;
        }

        $editId = (int)($_POST['id'] ?? 0);
        $titleEn = trim((string)($_POST['title_en'] ?? ''));
        if ($titleEn === '') throw new RuntimeException('English title is required.');
        $blockKey = trim((string)($_POST['block_key'] ?? '')) ?: slugify($titleEn);
        $blockKey = slugify($blockKey);
        $keyCheck = $pdo->prepare('SELECT id FROM content_blocks WHERE block_key=? AND id<>? LIMIT 1');
        $keyCheck->execute([$blockKey, $editId]);
        if ($keyCheck->fetch()) throw new RuntimeException('This block key is already used. Choose another key.');

        $imagePath = trim((string)($_POST['image'] ?? ''));
        $uploadedImage = upload_image_file('image_file', 'uploads/media', $blockKey, 5 * 1024 * 1024);
        if ($uploadedImage) $imagePath = $uploadedImage;

        $data = [
            $blockKey,
            trim((string)($_POST['placement'] ?? 'homepage')) ?: 'homepage',
            $titleEn,
            trim((string)($_POST['title_bn'] ?? '')),
            trim((string)($_POST['body_en'] ?? '')),
            trim((string)($_POST['body_bn'] ?? '')),
            $imagePath,
            trim((string)($_POST['button_label_en'] ?? '')),
            trim((string)($_POST['button_label_bn'] ?? '')),
            trim((string)($_POST['button_url'] ?? '')),
            (int)($_POST['sort_order'] ?? 0),
            isset($_POST['is_active']) ? 1 : 0,
        ];
        if ($editId) {
            $data[] = $editId;
            $stmt = $pdo->prepare('UPDATE content_blocks SET block_key=?, placement=?, title_en=?, title_bn=?, body_en=?, body_bn=?, image=?, button_label_en=?, button_label_bn=?, button_url=?, sort_order=?, is_active=?, updated_at=NOW() WHERE id=?');
            $stmt->execute($data);
        } else {
            $stmt = $pdo->prepare('INSERT INTO content_blocks (block_key, placement, title_en, title_bn, body_en, body_bn, image, button_label_en, button_label_bn, button_url, sort_order, is_active, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())');
            $stmt->execute($data);
        }
        log_admin_action('content_block_save', 'Saved content block: ' . $blockKey);
        header('Location: content-blocks.php'); exit;
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

$blocks = get_content_blocks(null, false);
$placements = ['homepage', 'blog', 'product', 'footer', 'campaign'];
admin_header($block ? 'Edit Content Block' : 'Content Blocks');
?>
<?php if($flash): ?><div class="success"><?= e($flash) ?></div><?php endif; ?>
<?php if($error): ?><div class="alert"><?= e($error) ?></div><?php endif; ?>

<section class="panel admin-list-head-v30 content-block-head-v43">
    <div>
        <span class="panel-eyebrow">v43 Reusable Blocks</span>
        <h2>Create once, reuse in homepage and blog editor.</h2>
        <p>Use blocks for campaign messages, catalogue CTAs, distributor notes, product highlights and trade communication.</p>
    </div>
    <div class="admin-list-actions-v30"><a class="button secondary" href="content-blocks.php">+ New Block</a><a class="button ghost" href="media.php">Media Library</a></div>
</section>

<section class="admin-grid two-col wide-right content-block-admin-v43">
    <div class="panel table-wrap admin-table-panel-v30">
        <div class="bulk-toolbar-v30"><strong><?= count($blocks) ?> block(s)</strong><span>Copy a block key and insert it in blog content as [block:block-key]</span></div>
        <table class="admin-data-table-v30">
            <thead><tr><th>Block</th><th>Placement</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach($blocks as $item): ?>
                <tr>
                    <td data-label="Block"><strong><?= e($item['title_en']) ?></strong><br><small><code><?= e($item['block_key']) ?></code></small></td>
                    <td data-label="Placement"><?= e($item['placement']) ?><br><small>Order: <?= (int)$item['sort_order'] ?></small></td>
                    <td data-label="Status"><span class="status-pill-v30 <?= $item['is_active'] ? 'active' : 'hidden' ?>"><?= $item['is_active'] ? 'Active' : 'Hidden' ?></span></td>
                    <td data-label="Actions" class="row-actions-v30">
                        <a class="mini-button" href="content-blocks.php?id=<?= (int)$item['id'] ?>">Edit</a>
                        <button type="button" class="mini-button neutral" data-copy-text="[block:<?= e($item['block_key']) ?>]">Copy Shortcode</button>
                        <form method="post" class="inline-admin-form-v43"><?= csrf_field() ?><input type="hidden" name="action" value="toggle"><input type="hidden" name="id" value="<?= (int)$item['id'] ?>"><button class="mini-button neutral" type="submit"><?= $item['is_active'] ? 'Hide' : 'Show' ?></button></form>
                        <form method="post" class="inline-admin-form-v43" data-confirm-form="Delete this content block?"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$item['id'] ?>"><button class="danger" type="submit">Delete</button></form>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if(!$blocks): ?><tr><td colspan="4">No content blocks found.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>

    <form class="panel form-grid enhanced-form-v30" method="post" enctype="multipart/form-data">
        <?= csrf_field() ?><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?= (int)($block['id'] ?? 0) ?>">
        <div class="form-intro-v30 full"><span><?= $block ? 'Edit Reusable Block' : 'Create Reusable Block' ?></span><p>Homepage placement renders automatically. Other placements can be inserted into blog content using shortcode.</p></div>
        <label>Block Key<input name="block_key" value="<?= e($block['block_key'] ?? '') ?>" placeholder="auto-created from title"><small>Use in blog: [block:your-key]</small></label>
        <label>Placement<select name="placement"><?php foreach($placements as $place): ?><option value="<?= e($place) ?>" <?= ($block['placement'] ?? 'homepage')===$place?'selected':'' ?>><?= e($place) ?></option><?php endforeach; ?></select></label>
        <label>Title English<input name="title_en" value="<?= e($block['title_en'] ?? '') ?>" required></label>
        <label>Title Bangla<input name="title_bn" value="<?= e($block['title_bn'] ?? '') ?>"></label>
        <label class="full">Body English<textarea name="body_en" rows="4"><?= e($block['body_en'] ?? '') ?></textarea></label>
        <label class="full">Body Bangla<textarea name="body_bn" rows="4"><?= e($block['body_bn'] ?? '') ?></textarea></label>
        <label class="full">Image Path<input name="image" value="<?= e($block['image'] ?? '') ?>" placeholder="uploads/media/example.webp"><small>Paste from Media Library or upload below.</small></label>
        <label class="full">Upload/Replace Image<input type="file" name="image_file" accept=".jpg,.jpeg,.png,.webp" data-image-preview="#blockImagePreview"></label>
        <div class="image-preview-box-v30"><img id="blockImagePreview" class="preview" src="<?= !empty($block['image']) ? '../' . e($block['image']) : '' ?>" alt="" <?= empty($block['image']) ? 'hidden' : '' ?>><small>Block image preview</small></div>
        <label>Button Label English<input name="button_label_en" value="<?= e($block['button_label_en'] ?? '') ?>"></label>
        <label>Button Label Bangla<input name="button_label_bn" value="<?= e($block['button_label_bn'] ?? '') ?>"></label>
        <label class="full">Button URL<input name="button_url" value="<?= e($block['button_url'] ?? '') ?>" placeholder="#network, catalogue, https://..."></label>
        <label>Sort Order<input type="number" name="sort_order" value="<?= e((string)($block['sort_order'] ?? 0)) ?>"></label>
        <label class="check"><input type="checkbox" name="is_active" <?= ($block['is_active'] ?? 1) ? 'checked' : '' ?>> Active</label>
        <div class="full form-submit-row-v30"><button class="button" type="submit">Save Block</button><a class="button secondary" href="content-blocks.php">Cancel</a></div>
    </form>
</section>
<?php admin_footer(); ?>
