<?php
require_once __DIR__ . '/_layout.php';
require_admin();
$pdo = db();
ensure_phase3_product_schema();

$flash = '';
$error = '';
if (isset($_GET['toggle'], $_GET['token']) && hash_equals($_SESSION['csrf_token'] ?? '', (string)$_GET['token'])) {
    try {
        $id = (int)$_GET['toggle'];
        $stmt = $pdo->prepare('UPDATE products SET is_active = IF(is_active=1,0,1), updated_at = NOW() WHERE id = ?');
        $stmt->execute([$id]);
        log_admin_action('product_visibility_toggle', 'Product ID ' . $id . ' visibility changed.');
        header('Location: products.php');
        exit;
    } catch (Throwable $e) { $error = $e->getMessage(); }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        verify_csrf();
        $action = $_POST['action'] ?? '';
        if ($action === 'save_order') {
            $orders = $_POST['sort_order'] ?? [];
            if (is_array($orders)) {
                $stmt = $pdo->prepare('UPDATE products SET sort_order = ?, updated_at = NOW() WHERE id = ?');
                foreach ($orders as $id => $order) {
                    $stmt->execute([(int)$order, (int)$id]);
                }
            }
            log_admin_action('product_sort_order', 'Product sort order was updated.');
            $flash = 'Product order saved.';
        }
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

$q = trim((string)($_GET['q'] ?? ''));
$brand = trim((string)($_GET['brand'] ?? ''));
$status = trim((string)($_GET['status'] ?? ''));
$where = [];
$params = [];
if ($q !== '') {
    $where[] = '(name_en LIKE ? OR name_bn LIKE ? OR slug LIKE ? OR category LIKE ? OR badge LIKE ?)';
    $like = '%' . $q . '%';
    array_push($params, $like, $like, $like, $like, $like);
}
if (in_array($brand, ['A TO Z', 'SIGNAL'], true)) {
    $where[] = 'brand = ?';
    $params[] = $brand;
}
if ($status === 'active') $where[] = 'is_active = 1';
if ($status === 'hidden') $where[] = 'is_active = 0';
$sql = 'SELECT * FROM products ' . ($where ? 'WHERE ' . implode(' AND ', $where) . ' ' : '') . 'ORDER BY sort_order ASC, id DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();
$totalProducts = (int)$pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
$activeProducts = (int)$pdo->query('SELECT COUNT(*) FROM products WHERE is_active=1')->fetchColumn();

admin_header('Products');
?>
<?php if($flash): ?><div class="success"><?= e($flash) ?></div><?php endif; ?>
<?php if($error): ?><div class="alert"><?= e($error) ?></div><?php endif; ?>

<section class="panel admin-list-head-v30">
    <div>
        <span class="panel-eyebrow">Product Control</span>
        <h2>Manage portfolio visibility, order and product details.</h2>
        <p><?= $activeProducts ?> active out of <?= $totalProducts ?> product(s). Use filters to find items fast, then toggle visibility directly from this list.</p>
    </div>
    <div class="admin-list-actions-v30">
        <a class="button" href="product-form.php">+ Add Product</a>
        <a class="button secondary" href="<?= e(home_url('products')) ?>" target="_blank" rel="noopener">View Products</a>
    </div>
</section>

<section class="panel admin-filter-panel-v30">
    <form class="admin-filters-v30" method="get">
        <label>Search
            <input name="q" value="<?= e($q) ?>" placeholder="Name, slug, category, badge...">
        </label>
        <label>Brand
            <select name="brand">
                <option value="">All brands</option>
                <option value="A TO Z" <?= $brand==='A TO Z'?'selected':'' ?>>A TO Z</option>
                <option value="SIGNAL" <?= $brand==='SIGNAL'?'selected':'' ?>>SIGNAL</option>
            </select>
        </label>
        <label>Status
            <select name="status">
                <option value="">All status</option>
                <option value="active" <?= $status==='active'?'selected':'' ?>>Active</option>
                <option value="hidden" <?= $status==='hidden'?'selected':'' ?>>Hidden</option>
            </select>
        </label>
        <div class="filter-actions-v30"><button class="button" type="submit">Filter</button><a class="button ghost" href="products.php">Reset</a></div>
    </form>
</section>

<form method="post" class="panel table-wrap admin-table-panel-v30"><?= csrf_field() ?>
    <input type="hidden" name="action" value="save_order">
    <div class="bulk-toolbar-v30">
        <strong><?= count($products) ?> product(s) found</strong>
        <button class="button secondary" type="submit">Save Sort Order</button>
    </div>
    <table class="admin-data-table-v30">
        <thead><tr><th>Image</th><th>Product</th><th>Brand</th><th>Badge</th><th>Specs</th><th>Order</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach($products as $p): ?>
            <tr>
                <td data-label="Image"><img class="thumb" src="../<?= e($p['image']) ?>" alt=""></td>
                <td data-label="Product"><strong><?= e($p['name_en']) ?></strong><br><small><?= e($p['name_bn']) ?></small><br><small class="muted-mini-v30"><?= e($p['slug']) ?></small></td>
                <td data-label="Brand"><span class="brand-chip-v30 <?= $p['brand']==='SIGNAL'?'signal':'atoz' ?>"><?= e($p['brand']) ?></span></td>
                <td data-label="Badge"><?= e($p['badge']) ?></td>
                <?php $specReady = count(product_specs($p)); $faqReady = count(product_faqs($p)); $galleryReady = max(0, count(product_gallery($p)) - 1); ?>
                <td data-label="Specs"><span class="product-readiness-v32"><b><?= $specReady ?></b> specs</span><small class="muted-mini-v30"><?= $galleryReady ?> gallery · <?= $faqReady ?> FAQ</small></td>
                <td data-label="Order"><input class="order-input-v30" type="number" name="sort_order[<?= (int)$p['id'] ?>]" value="<?= (int)$p['sort_order'] ?>" aria-label="Sort order for <?= e($p['name_en']) ?>"></td>
                <td data-label="Status"><span class="status-pill-v30 <?= $p['is_active'] ? 'active' : 'hidden' ?>"><?= $p['is_active'] ? 'Active' : 'Hidden' ?></span></td>
                <td data-label="Actions" class="row-actions-v30">
                    <a class="mini-button" href="product-form.php?id=<?= (int)$p['id'] ?>">Edit</a>
                    <a class="mini-button neutral" href="products.php?toggle=<?= (int)$p['id'] ?>&token=<?= e(csrf_token()) ?>"><?= $p['is_active'] ? 'Hide' : 'Show' ?></a>
                    <a class="danger" data-confirm="Delete this product?" href="product-delete.php?id=<?= (int)$p['id'] ?>&token=<?= e(csrf_token()) ?>">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if(!$products): ?><tr><td colspan="8">No products matched your filters.</td></tr><?php endif; ?>
        </tbody>
    </table>
</form>
<?php admin_footer(); ?>
