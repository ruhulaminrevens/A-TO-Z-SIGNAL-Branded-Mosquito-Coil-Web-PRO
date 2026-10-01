<?php
require_once __DIR__ . '/_layout.php';
require_admin();
ensure_phase4_blog_schema();
ensure_phase43_media_content_schema();
$pdo = db();
$id = (int)($_GET['id'] ?? 0);
$post = null;
if ($id) { $stmt=$pdo->prepare('SELECT * FROM blog_posts WHERE id=?'); $stmt->execute([$id]); $post=$stmt->fetch(); }
$error='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    try {
        verify_csrf();
        $titleEn = trim($_POST['title_en'] ?? '');
        if ($titleEn==='') throw new RuntimeException('English title is required.');
        $slug = trim($_POST['slug'] ?? '') ?: slugify($titleEn);
        $slugCheck = $pdo->prepare('SELECT id FROM blog_posts WHERE slug = ? AND id <> ? LIMIT 1');
        $slugCheck->execute([$slug, $id]);
        if ($slugCheck->fetch()) throw new RuntimeException('This blog slug is already used. Please choose another slug.');
        $imagePath = $post['image'] ?? '';
        $uploadedImage = upload_image_file('image', 'uploads/blog', $slug, 5 * 1024 * 1024);
        if ($uploadedImage) $imagePath = $uploadedImage;
        $data = [
            $titleEn,
            trim($_POST['title_bn'] ?? ''),
            $slug,
            trim($_POST['category'] ?? '') ?: blog_default_category(),
            trim($_POST['tags'] ?? ''),
            trim($_POST['excerpt_en'] ?? ''),
            trim($_POST['excerpt_bn'] ?? ''),
            trim($_POST['content_en'] ?? ''),
            trim($_POST['content_bn'] ?? ''),
            json_encode(normalize_json_list($_POST['content_block_keys'] ?? ''), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            trim($_POST['editor_notes'] ?? ''),
            $imagePath,
            trim($_POST['published_at'] ?? '') ?: null,
            (int)($_POST['sort_order'] ?? 0),
            isset($_POST['is_featured'])?1:0,
            isset($_POST['is_published'])?1:0,
            trim($_POST['seo_title'] ?? ''),
            trim($_POST['seo_description'] ?? ''),
        ];
        if ($id) {
            $data[]=$id;
            $stmt=$pdo->prepare('UPDATE blog_posts SET title_en=?, title_bn=?, slug=?, category=?, tags=?, excerpt_en=?, excerpt_bn=?, content_en=?, content_bn=?, content_blocks_json=?, editor_notes=?, image=?, published_at=?, sort_order=?, is_featured=?, is_published=?, seo_title=?, seo_description=?, updated_at=NOW() WHERE id=?');
            $stmt->execute($data);
        } else {
            $stmt=$pdo->prepare('INSERT INTO blog_posts (title_en,title_bn,slug,category,tags,excerpt_en,excerpt_bn,content_en,content_bn,content_blocks_json,editor_notes,image,published_at,sort_order,is_featured,is_published,seo_title,seo_description,created_at,updated_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW(),NOW())');
            $stmt->execute($data);
        }
        log_admin_action('blog_save', 'Blog post ' . ($id ? ('ID ' . $id . ' updated') : 'created') . '.');
        header('Location: blogs.php'); exit;
    } catch (Throwable $e) { $error=$e->getMessage(); }
}
$categories = blog_categories();
$mediaAssets = get_media_assets(['type' => 'images'], 36);
$contentBlockKeys = content_block_key_options();
$contentBlockText = $post ? implode("
", normalize_json_list($post['content_blocks_json'] ?? '')) : '';
admin_header($id ? 'Edit Blog Post' : 'Add Blog Post');
?>
<?php if($error): ?><div class="alert"><?= e($error) ?></div><?php endif; ?>
<form class="panel form-grid enhanced-form-v30 blog-editor-form-v33" method="post" enctype="multipart/form-data"><?= csrf_field() ?>
<div class="form-intro-v30 full"><span>v43 Rich Blog Editor</span><p>Use quick snippets, reusable blocks, media shortcodes, headings, quotes, CTAs and SEO fields from one editor.</p></div>
<label>Title English<input name="title_en" value="<?= e($post['title_en'] ?? '') ?>" required></label>
<label>Title Bangla<input name="title_bn" value="<?= e($post['title_bn'] ?? '') ?>"></label>
<label>Slug<input name="slug" value="<?= e($post['slug'] ?? '') ?>" placeholder="auto-generated if blank"><small>Unique URL slug for the blog details page.</small></label>
<label>Published Date<input type="date" name="published_at" value="<?= e((string)($post['published_at'] ?? date('Y-m-d'))) ?>"></label>
<label>Category<input name="category" list="blogCategoryList" value="<?= e($post['category'] ?? blog_default_category()) ?>" placeholder="Sales Meet, Product Launch, Brand Update"><datalist id="blogCategoryList"><?php foreach($categories as $cat): ?><option value="<?= e($cat) ?>"><?php endforeach; ?></datalist><small>Use 3–5 consistent categories for cleaner filtering.</small></label>
<label>Tags<input name="tags" value="<?= e($post['tags'] ?? '') ?>" placeholder="sales meet, launch, signal"><small>Separate tags with comma.</small></label>
<label class="full">Excerpt English<textarea name="excerpt_en" rows="3"><?= e($post['excerpt_en'] ?? '') ?></textarea></label>
<label class="full">Excerpt Bangla<textarea name="excerpt_bn" rows="3"><?= e($post['excerpt_bn'] ?? '') ?></textarea></label>
<div class="blog-editor-toolbar-v33 blog-editor-toolbar-v43 full">
    <span>Rich editor shortcuts</span>
    <button type="button" data-editor-insert="## Section Heading">H2</button>
    <button type="button" data-editor-insert="### Small Heading">H3</button>
    <button type="button" data-editor-insert="- Bullet point one
- Bullet point two">Bullets</button>
    <button type="button" data-editor-insert="> Important quote or highlight">Quote</button>
    <button type="button" data-editor-insert="[button: Contact Sales | #contact]">CTA</button>
    <button type="button" data-editor-insert="[image: uploads/media/example.webp | Image caption]">Image</button>
    <?php foreach(array_slice($contentBlockKeys, 0, 6) as $key): ?><button type="button" data-editor-insert="[block:<?= e($key) ?>]">Block: <?= e($key) ?></button><?php endforeach; ?>
</div>
<label class="full">Content English<textarea name="content_en" rows="11" data-rich-editor data-word-count="#contentEnCount"><?= e($post['content_en'] ?? '') ?></textarea><small id="contentEnCount">0 words</small></label>
<label class="full">Content Bangla<textarea name="content_bn" rows="9" data-word-count="#contentBnCount"><?= e($post['content_bn'] ?? '') ?></textarea><small id="contentBnCount">0 words</small></label>
<label class="full">Attached Content Block Keys<textarea name="content_block_keys" rows="3" placeholder="One key per line. Example: distribution-support"><?= e($contentBlockText) ?></textarea><small>These blocks render below the blog body. You can also insert inline blocks with [block:block-key].</small></label>
<label class="full">Editor Notes<textarea name="editor_notes" rows="2" placeholder="Internal note for admin only"><?= e($post['editor_notes'] ?? '') ?></textarea></label>
<div class="panel blog-media-picker-v43 full">
    <div class="bulk-toolbar-v30"><strong>Media Library Quick Insert</strong><span>Click Copy Image Shortcode, then paste into content.</span></div>
    <div class="blog-media-grid-v43">
        <?php foreach($mediaAssets as $asset): ?>
            <button type="button" data-editor-insert="[image: <?= e($asset['file_path']) ?> | <?= e($asset['alt_text'] ?: $asset['title']) ?>]"><img src="../<?= e($asset['file_path']) ?>" alt=""><span><?= e($asset['title'] ?: basename($asset['file_path'])) ?></span></button>
        <?php endforeach; ?>
        <?php if(!$mediaAssets): ?><p>No media indexed yet. Open Media Library and click Sync Uploads.</p><?php endif; ?>
    </div>
</div>
<label class="full">SEO Title<input name="seo_title" value="<?= e($post['seo_title'] ?? '') ?>" maxlength="220" placeholder="Optional custom title for Google/Facebook"></label>
<label class="full">SEO Description<textarea name="seo_description" rows="3" placeholder="Optional custom description for search/social share"><?= e($post['seo_description'] ?? '') ?></textarea></label>
<label>Sort Order<input type="number" name="sort_order" value="<?= e((string)($post['sort_order'] ?? 0)) ?>"></label>
<label>Feature Image<input type="file" name="image" accept=".jpg,.jpeg,.png,.webp" data-image-preview="#blogImagePreview"><small>JPG, PNG or WEBP. Maximum 5MB.</small></label>
<label class="check"><input type="checkbox" name="is_featured" <?= !empty($post['is_featured']) ? 'checked' : '' ?>> Featured Story</label>
<label class="check"><input type="checkbox" name="is_published" <?= ($post['is_published'] ?? 1) ? 'checked' : '' ?>> Published</label>
<div class="image-preview-box-v30"><img id="blogImagePreview" class="preview" src="<?= !empty($post['image']) ? '../' . e($post['image']) : '' ?>" alt="" <?= empty($post['image']) ? 'hidden' : '' ?>><small>Live image preview</small></div>
<div class="full form-submit-row-v30"><button class="button" type="submit">Save Blog Post</button><?php if($id): ?><a class="button ghost" href="<?= e(blog_post_url($post ?? [])) ?>" target="_blank">Preview</a><?php endif; ?><a class="button secondary" href="blogs.php">Cancel</a></div>
</form>
<?php admin_footer(); ?>
