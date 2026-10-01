<?php
require_once __DIR__ . '/_layout.php';
require_admin();
ensure_phase4_blog_schema();
$pdo = db();
$flash = '';
$error = '';

if (isset($_GET['toggle'], $_GET['token']) && hash_equals($_SESSION['csrf_token'] ?? '', (string)$_GET['token'])) {
    try {
        $id = (int)$_GET['toggle'];
        $stmt = $pdo->prepare('UPDATE blog_posts SET is_published = IF(is_published=1,0,1), updated_at = NOW() WHERE id = ?');
        $stmt->execute([$id]);
        log_admin_action('blog_publish_toggle', 'Blog ID ' . $id . ' publish status changed.');
        header('Location: blogs.php');
        exit;
    } catch (Throwable $e) { $error = $e->getMessage(); }
}
if (isset($_GET['feature'], $_GET['token']) && hash_equals($_SESSION['csrf_token'] ?? '', (string)$_GET['token'])) {
    try {
        $id = (int)$_GET['feature'];
        $stmt = $pdo->prepare('UPDATE blog_posts SET is_featured = IF(is_featured=1,0,1), updated_at = NOW() WHERE id = ?');
        $stmt->execute([$id]);
        log_admin_action('blog_feature_toggle', 'Blog ID ' . $id . ' featured status changed.');
        header('Location: blogs.php');
        exit;
    } catch (Throwable $e) { $error = $e->getMessage(); }
}

$q = trim((string)($_GET['q'] ?? ''));
$status = trim((string)($_GET['status'] ?? ''));
$year = trim((string)($_GET['year'] ?? ''));
$category = trim((string)($_GET['category'] ?? ''));
$featuredFilter = trim((string)($_GET['featured'] ?? ''));
$where = [];
$params = [];
if ($q !== '') {
    $like = '%' . $q . '%';
    $where[] = '(title_en LIKE ? OR title_bn LIKE ? OR slug LIKE ? OR excerpt_en LIKE ? OR excerpt_bn LIKE ? OR tags LIKE ? OR category LIKE ?)';
    array_push($params, $like, $like, $like, $like, $like, $like, $like);
}
if ($status === 'published') $where[] = 'is_published = 1';
if ($status === 'draft') $where[] = 'is_published = 0';
if ($featuredFilter === 'featured') $where[] = 'is_featured = 1';
if ($category !== '') { $where[] = 'category = ?'; $params[] = $category; }
if ($year !== '' && preg_match('/^\d{4}$/', $year)) { $where[] = 'YEAR(published_at) = ?'; $params[] = $year; }
$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';
$stmt = $pdo->prepare("SELECT * FROM blog_posts {$whereSql} ORDER BY is_featured DESC, COALESCE(published_at, created_at) DESC, sort_order ASC, id DESC");
$stmt->execute($params);
$posts = $stmt->fetchAll();
$years = [];
try { $years = $pdo->query('SELECT DISTINCT YEAR(published_at) AS y FROM blog_posts WHERE published_at IS NOT NULL ORDER BY y DESC')->fetchAll(); } catch (Throwable $e) {}
$categories = blog_categories();
$total = (int)$pdo->query('SELECT COUNT(*) FROM blog_posts')->fetchColumn();
$published = (int)$pdo->query('SELECT COUNT(*) FROM blog_posts WHERE is_published=1')->fetchColumn();
$featuredCount = (int)$pdo->query('SELECT COUNT(*) FROM blog_posts WHERE is_featured=1')->fetchColumn();
admin_header('Blog / Archive');
?>
<?php if($flash): ?><div class="success"><?= e($flash) ?></div><?php endif; ?>
<?php if($error): ?><div class="alert"><?= e($error) ?></div><?php endif; ?>

<section class="panel admin-list-head-v30 admin-list-head-v33">
    <div>
        <span class="panel-eyebrow">Phase 4 Archive CMS</span>
        <h2>Manage categories, tags, featured stories and SEO-ready posts.</h2>
        <p><?= $published ?> published, <?= $featuredCount ?> featured out of <?= $total ?> post(s). Use category and tag structure for a cleaner public archive.</p>
    </div>
    <div class="admin-list-actions-v30"><a class="button" href="blog-form.php">+ Add Blog Post</a><a class="button secondary" href="<?= e(page_url('blogs')) ?>" target="_blank">View Blog</a></div>
</section>

<section class="panel admin-filter-panel-v30">
    <form class="admin-filters-v30 admin-filters-v33" method="get">
        <label>Search<input name="q" value="<?= e($q) ?>" placeholder="Title, slug, category, tags..."></label>
        <label>Status<select name="status"><option value="">All status</option><option value="published" <?= $status==='published'?'selected':'' ?>>Published</option><option value="draft" <?= $status==='draft'?'selected':'' ?>>Draft</option></select></label>
        <label>Category<select name="category"><option value="">All categories</option><?php foreach($categories as $cat): ?><option value="<?= e($cat) ?>" <?= $category===$cat?'selected':'' ?>><?= e($cat) ?></option><?php endforeach; ?></select></label>
        <label>Year<select name="year"><option value="">All years</option><?php foreach($years as $yr): $y=(string)($yr['y'] ?? ''); if($y==='') continue; ?><option value="<?= e($y) ?>" <?= $year===$y?'selected':'' ?>><?= e($y) ?></option><?php endforeach; ?></select></label>
        <label>Featured<select name="featured"><option value="">All posts</option><option value="featured" <?= $featuredFilter==='featured'?'selected':'' ?>>Featured only</option></select></label>
        <div class="filter-actions-v30"><button class="button" type="submit">Filter</button><a class="button ghost" href="blogs.php">Reset</a></div>
    </form>
</section>

<div class="panel table-wrap admin-table-panel-v30">
    <div class="bulk-toolbar-v30"><strong><?= count($posts) ?> post(s) found</strong><span>Tip: mark your strongest post as featured and keep categories simple.</span></div>
    <table class="admin-data-table-v30 admin-data-table-v33">
        <thead><tr><th>Image</th><th>Title</th><th>Category / Tags</th><th>Date</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach($posts as $post): ?>
            <tr>
                <td data-label="Image"><?php if(!empty($post['image'])): ?><img class="mini-thumb" src="../<?= e($post['image']) ?>" alt=""><?php endif; ?></td>
                <td data-label="Title"><strong><?= e($post['title_en']) ?></strong><?php if(!empty($post['is_featured'])): ?><span class="admin-featured-pill-v33">Featured</span><?php endif; ?><br><small><?= e($post['title_bn']) ?></small><br><small class="muted-mini-v30"><?= e($post['slug']) ?></small></td>
                <td data-label="Category"><strong><?= e($post['category'] ?: blog_default_category()) ?></strong><br><small><?= e($post['tags'] ?? '') ?></small></td>
                <td data-label="Date"><?= e((string)$post['published_at']) ?></td>
                <td data-label="Status"><span class="status-pill-v30 <?= $post['is_published'] ? 'active' : 'hidden' ?>"><?= $post['is_published'] ? 'Published' : 'Draft' ?></span></td>
                <td data-label="Actions" class="row-actions-v30"><a class="mini-button" href="blog-form.php?id=<?= (int)$post['id'] ?>">Edit</a><a class="mini-button neutral" href="blogs.php?feature=<?= (int)$post['id'] ?>&token=<?= e(csrf_token()) ?>"><?= $post['is_featured'] ? 'Unfeature' : 'Feature' ?></a><a class="mini-button neutral" href="blogs.php?toggle=<?= (int)$post['id'] ?>&token=<?= e(csrf_token()) ?>"><?= $post['is_published'] ? 'Draft' : 'Publish' ?></a><a class="danger" data-confirm="Delete this blog post?" href="blog-delete.php?id=<?= (int)$post['id'] ?>&token=<?= e(csrf_token()) ?>">Delete</a></td>
            </tr>
        <?php endforeach; ?>
        <?php if(!$posts): ?><tr><td colspan="6">No posts matched your filters.</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>
<?php admin_footer(); ?>
