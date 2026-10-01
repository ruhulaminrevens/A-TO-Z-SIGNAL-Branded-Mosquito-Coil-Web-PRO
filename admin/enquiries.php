<?php
require_once __DIR__ . '/_layout.php';
require_admin();
$pdo = db();
ensure_enquiry_crm_columns();
ensure_enquiry_notes_schema();

function enquiry_query_parts_v41(array $filters): array {
    $where = [];
    $params = [];
    if (($filters['q'] ?? '') !== '') {
        $like = '%' . $filters['q'] . '%';
        $where[] = '(name LIKE ? OR phone LIKE ? OR company_name LIKE ? OR district LIKE ? OR business_address LIKE ? OR business_type LIKE ? OR interested_brand LIKE ? OR product_name LIKE ? OR product_slug LIKE ? OR enquiry_type LIKE ? OR lead_source LIKE ? OR message LIKE ? OR admin_note LIKE ? OR phone_normalized LIKE ?)';
        array_push($params, $like, $like, $like, $like, $like, $like, $like, $like, $like, $like, $like, $like, $like, $like);
    }
    if (($filters['status'] ?? '') !== '') {
        $where[] = 'status = ?';
        $params[] = $filters['status'];
    }
    if (($filters['district'] ?? '') !== '') {
        $where[] = 'district = ?';
        $params[] = $filters['district'];
    }
    if (($filters['business_type'] ?? '') !== '') {
        $where[] = 'business_type = ?';
        $params[] = $filters['business_type'];
    }
    if (($filters['interested_brand'] ?? '') !== '') {
        $where[] = 'interested_brand = ?';
        $params[] = $filters['interested_brand'];
    }
    if (($filters['enquiry_type'] ?? '') !== '') {
        $where[] = 'enquiry_type = ?';
        $params[] = $filters['enquiry_type'];
    }
    if (($filters['product_slug'] ?? '') !== '') {
        $where[] = 'product_slug = ?';
        $params[] = $filters['product_slug'];
    }
    if (($filters['lead_source'] ?? '') !== '') {
        $where[] = 'lead_source = ?';
        $params[] = $filters['lead_source'];
    }
    if (($filters['follow'] ?? '') === 'due') {
        $where[] = 'follow_up_at IS NOT NULL AND follow_up_at <= CURDATE() AND ' . enquiry_active_status_condition('status');
    } elseif (($filters['follow'] ?? '') === 'upcoming') {
        $where[] = 'follow_up_at > CURDATE() AND ' . enquiry_active_status_condition('status');
    }
    if (($filters['duplicate'] ?? '') === 'yes') {
        $where[] = "phone_normalized IS NOT NULL AND phone_normalized <> '' AND phone_normalized IN (SELECT phone_normalized FROM (SELECT phone_normalized FROM distributor_enquiries WHERE phone_normalized IS NOT NULL AND phone_normalized <> '' GROUP BY phone_normalized HAVING COUNT(*) > 1) dup)";
    }
    return [$where, $params];
}

$statuses = enquiry_status_options();
$brands = enquiry_brand_options();
$types = enquiry_type_options();
$filters = [
    'q' => trim((string)($_GET['q'] ?? '')),
    'status' => trim((string)($_GET['status'] ?? '')),
    'district' => trim((string)($_GET['district'] ?? '')),
    'business_type' => trim((string)($_GET['business_type'] ?? '')),
    'interested_brand' => trim((string)($_GET['interested_brand'] ?? '')),
    'enquiry_type' => trim((string)($_GET['enquiry_type'] ?? '')),
    'product_slug' => trim((string)($_GET['product_slug'] ?? '')),
    'lead_source' => trim((string)($_GET['lead_source'] ?? '')),
    'follow' => trim((string)($_GET['follow'] ?? '')),
    'duplicate' => trim((string)($_GET['duplicate'] ?? '')),
];
if (!array_key_exists($filters['status'], $statuses)) $filters['status'] = '';
if ($filters['interested_brand'] !== '' && !in_array($filters['interested_brand'], $brands, true)) $filters['interested_brand'] = '';
if ($filters['enquiry_type'] !== '' && !in_array($filters['enquiry_type'], $types, true)) $filters['enquiry_type'] = '';
if (!in_array($filters['follow'], ['', 'due', 'upcoming'], true)) $filters['follow'] = '';
if ($filters['duplicate'] !== 'yes') $filters['duplicate'] = '';

$flash = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        verify_csrf();
        $action = (string)($_POST['action'] ?? 'update');
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) throw new RuntimeException('Invalid lead selected.');

        if ($action === 'delete') {
            try { $pdo->prepare('DELETE FROM enquiry_notes WHERE enquiry_id=?')->execute([$id]); } catch (Throwable $e) {}
            $stmt = $pdo->prepare('DELETE FROM distributor_enquiries WHERE id=?');
            $stmt->execute([$id]);
            log_admin_action('enquiry_delete', 'Distributor enquiry ID ' . $id . ' deleted.');
            header('Location: enquiries.php');
            exit;
        }

        $stmt = $pdo->prepare('SELECT * FROM distributor_enquiries WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $existing = $stmt->fetch();
        if (!$existing) throw new RuntimeException('Lead not found.');

        $oldStatus = (string)($existing['status'] ?? 'new');
        if (!array_key_exists($oldStatus, $statuses)) $oldStatus = 'new';
        $oldFollowUp = (string)($existing['follow_up_at'] ?? '');

        $status = (string)($_POST['status'] ?? 'new');
        if (!array_key_exists($status, $statuses)) $status = 'new';
        $summary = trim((string)($_POST['admin_note'] ?? ''));
        $noteBody = trim((string)($_POST['note_body'] ?? ''));
        $followUp = trim((string)($_POST['follow_up_at'] ?? '')) ?: null;
        $handledAtSql = in_array($status, ['contacted','interested','converted','not_interested','rejected'], true) ? 'handled_at = COALESCE(handled_at, NOW()),' : '';
        $stmt = $pdo->prepare("UPDATE distributor_enquiries SET status = ?, admin_note = ?, follow_up_at = ?, {$handledAtSql} updated_at = NOW() WHERE id = ?");
        $stmt->execute([$status, $summary, $followUp, $id]);

        if ($noteBody === '' && ($oldStatus !== $status || $oldFollowUp !== (string)($followUp ?? ''))) {
            $noteBody = 'CRM update: status changed from ' . enquiry_status_label($oldStatus) . ' to ' . enquiry_status_label($status) . '.';
            if ((string)($followUp ?? '') !== '') $noteBody .= ' Next follow-up: ' . $followUp . '.';
        }
        enquiry_add_note($pdo, $id, $noteBody, $oldStatus, $status, $followUp);
        log_admin_action('enquiry_followup_update', 'Lead ID ' . $id . ' updated to status ' . $status . '.');
        $flash = 'Lead follow-up updated.';
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

[$where, $params] = enquiry_query_parts_v41($filters);
$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    $stmt = $pdo->prepare("SELECT * FROM distributor_enquiries {$whereSql} ORDER BY id DESC LIMIT 2000");
    $stmt->execute($params);
    $rows = $stmt->fetchAll();
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=distributor-enquiries-v41-' . date('Y-m-d') . '.csv');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['ID','Name','Phone','Normalized Phone','Company / Shop','District','Business Address','Business Type','Interested Brand','Enquiry Type','Product Slug','Product Name','Lead Source','Pipeline Status','Message','Admin Summary','Follow Up Date','Handled At','Updated At','Created At']);
    foreach ($rows as $row) {
        fputcsv($out, [
            $row['id'], $row['name'], $row['phone'], $row['phone_normalized'] ?? '', $row['company_name'] ?? '', $row['district'], $row['business_address'] ?? '', $row['business_type'], $row['interested_brand'] ?? 'Both', $row['enquiry_type'] ?? 'Distributor', $row['product_slug'] ?? '', $row['product_name'] ?? '', $row['lead_source'] ?? '', enquiry_status_label($row['status'] ?? 'new'), $row['message'], $row['admin_note'] ?? '', $row['follow_up_at'] ?? '', $row['handled_at'] ?? '', $row['updated_at'] ?? '', $row['created_at'] ?? ''
        ]);
    }
    fclose($out);
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM distributor_enquiries {$whereSql} ORDER BY id DESC LIMIT 500");
$stmt->execute($params);
$rows = $stmt->fetchAll();
$visibleLeadIds = array_map(static fn($row) => (int)$row['id'], $rows);
$noteHistory = enquiry_notes_for_ids($pdo, $visibleLeadIds, 5);

$duplicateGroups = enquiry_duplicate_phone_groups($pdo, 12);
$duplicateLookup = [];
foreach ($duplicateGroups as $group) {
    $key = (string)($group['phone_normalized'] ?? '');
    if ($key !== '') $duplicateLookup[$key] = (int)($group['total'] ?? 0);
}

$districts = get_distinct_values($pdo, 'distributor_enquiries', 'district');
$businessTypes = get_distinct_values($pdo, 'distributor_enquiries', 'business_type');
$leadSources = get_distinct_values($pdo, 'distributor_enquiries', 'lead_source');
$productSlugs = get_distinct_values($pdo, 'distributor_enquiries', 'product_slug');
$total = (int)$pdo->query('SELECT COUNT(*) FROM distributor_enquiries')->fetchColumn();
$newCount = (int)$pdo->query("SELECT COUNT(*) FROM distributor_enquiries WHERE status='new' OR status IS NULL OR status='' ")->fetchColumn();
$dueCount = (int)$pdo->query("SELECT COUNT(*) FROM distributor_enquiries WHERE follow_up_at IS NOT NULL AND follow_up_at <= CURDATE() AND " . enquiry_active_status_condition('status'))->fetchColumn();
$upcomingCount = (int)$pdo->query("SELECT COUNT(*) FROM distributor_enquiries WHERE follow_up_at > CURDATE() AND " . enquiry_active_status_condition('status'))->fetchColumn();
$convertedCount = (int)$pdo->query("SELECT COUNT(*) FROM distributor_enquiries WHERE status='converted'")->fetchColumn();
$activeCount = (int)$pdo->query('SELECT COUNT(*) FROM distributor_enquiries WHERE ' . enquiry_active_status_condition('status'))->fetchColumn();
$notesCount = (int)$pdo->query('SELECT COUNT(*) FROM enquiry_notes')->fetchColumn();
$duplicatePhoneCount = count($duplicateGroups);
$conversionRate = $total > 0 ? round(($convertedCount / $total) * 100, 1) : 0;
$pipelineCounts = enquiry_status_counts($pdo);
$pipelineRows = enquiry_pipeline_rows($pdo, 4);
$sourceCounts = enquiry_group_counts($pdo, 'lead_source', 6);
$brandCounts = enquiry_group_counts($pdo, 'interested_brand', 5);
$typeCounts = enquiry_group_counts($pdo, 'enquiry_type', 5);
$productCounts = enquiry_group_counts($pdo, 'product_name', 6);
$recentDue = [];
try {
    $recentDue = $pdo->query("SELECT * FROM distributor_enquiries WHERE follow_up_at IS NOT NULL AND follow_up_at <= DATE_ADD(CURDATE(), INTERVAL 3 DAY) AND " . enquiry_active_status_condition('status') . " ORDER BY follow_up_at ASC, id DESC LIMIT 8")->fetchAll();
} catch (Throwable $e) {}

$exportQuery = $_GET;
$exportQuery['export'] = 'csv';
$exportUrl = 'enquiries.php?' . http_build_query($exportQuery);
admin_header('Lead CRM Pipeline');
?>
<?php if($flash): ?><div class="success"><?= e($flash) ?></div><?php endif; ?>
<?php if($error): ?><div class="alert"><?= e($error) ?></div><?php endif; ?>

<section class="panel admin-list-head-v30 admin-list-head-v39 crm-hero-v41">
    <div>
        <span class="panel-eyebrow">Lead CRM · v41</span>
        <h2>Move every lead from new enquiry to final conversion.</h2>
        <p>Pipeline board, follow-up reminders, duplicate phone detection, note history and analytics are now connected in one admin view.</p>
    </div>
    <div class="lead-stats-v30 lead-stats-v39 crm-stats-v41">
        <span><b><?= $total ?></b><em>Total Leads</em></span>
        <span><b><?= $activeCount ?></b><em>Active Pipeline</em></span>
        <span><b><?= $dueCount ?></b><em>Due Follow-up</em></span>
        <span><b><?= $convertedCount ?></b><em>Converted</em></span>
        <span><b><?= $conversionRate ?>%</b><em>Conversion Rate</em></span>
        <span><b><?= $duplicatePhoneCount ?></b><em>Duplicate Groups</em></span>
    </div>
</section>

<section class="crm-pipeline-v41" aria-label="Lead pipeline board">
<?php foreach($statuses as $key => $label): $count = (int)($pipelineCounts[$key] ?? 0); ?>
    <article class="panel crm-stage-v41 status-<?= e($key) ?>">
        <div class="crm-stage-head-v41"><span><?= e($label) ?></span><b><?= $count ?></b></div>
        <div class="crm-stage-list-v41">
            <?php foreach(($pipelineRows[$key] ?? []) as $lead): ?>
                <a class="crm-mini-lead-v41" href="enquiries.php?status=<?= e($key) ?>&q=<?= urlencode((string)$lead['phone']) ?>">
                    <strong><?= e($lead['name']) ?></strong>
                    <small><?= e($lead['district']) ?> · <?= e($lead['interested_brand'] ?? 'Both') ?></small>
                    <?php if(!empty($lead['follow_up_at'])): ?><em>Follow-up: <?= e((string)$lead['follow_up_at']) ?></em><?php endif; ?>
                </a>
            <?php endforeach; ?>
            <?php if(empty($pipelineRows[$key])): ?><p>No leads in this stage.</p><?php endif; ?>
        </div>
    </article>
<?php endforeach; ?>
</section>

<section class="admin-grid two-col crm-insights-grid-v41">
    <div class="panel crm-reminders-v41">
        <div class="panel-title-row"><div><span class="panel-eyebrow">Follow-up Reminders</span><h2>Due now / next 3 days</h2></div><a class="small-link" href="enquiries.php?follow=due">Due only →</a></div>
        <div class="crm-reminder-list-v41">
            <?php foreach($recentDue as $lead): ?>
                <a href="enquiries.php?q=<?= urlencode((string)$lead['phone']) ?>">
                    <b><?= e($lead['name']) ?></b>
                    <span><?= e((string)$lead['follow_up_at']) ?> · <?= e($lead['district']) ?> · <?= e(enquiry_status_label($lead['status'] ?? 'new')) ?></span>
                </a>
            <?php endforeach; ?>
            <?php if(!$recentDue): ?><p>No due reminders right now.</p><?php endif; ?>
        </div>
    </div>
    <div class="panel crm-analytics-v41">
        <div class="panel-title-row"><div><span class="panel-eyebrow">Lead Analytics</span><h2>Where leads are coming from</h2></div><a class="small-link" href="<?= e($exportUrl) ?>">Export CSV →</a></div>
        <div class="crm-analytics-bars-v41">
            <?php foreach(['Source'=>$sourceCounts,'Brand'=>$brandCounts,'Type'=>$typeCounts] as $label => $items): ?>
                <div class="crm-mini-chart-v41"><b><?= e($label) ?></b>
                    <?php foreach($items as $item): $pct = $total > 0 ? min(100, round(((int)$item['total'] / $total) * 100)) : 0; ?>
                        <span><em><?= e((string)$item['label']) ?> · <?= (int)$item['total'] ?></em><i style="width:<?= (int)$pct ?>%"></i></span>
                    <?php endforeach; ?>
                    <?php if(!$items): ?><small>No data yet.</small><?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php if($duplicateGroups): ?>
<section class="panel crm-duplicates-v41">
    <div class="panel-title-row"><div><span class="panel-eyebrow">Duplicate Detection</span><h2>Phone numbers found in multiple leads</h2></div><a class="small-link" href="enquiries.php?duplicate=yes">View all duplicate leads →</a></div>
    <div class="crm-duplicate-grid-v41">
        <?php foreach($duplicateGroups as $group): ?>
            <a href="enquiries.php?q=<?= urlencode((string)$group['phone_normalized']) ?>&duplicate=yes"><b><?= e((string)$group['phone_normalized']) ?></b><span><?= (int)$group['total'] ?> leads · <?= e((string)($group['districts'] ?? '')) ?></span></a>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<section class="panel admin-filter-panel-v30">
    <form class="admin-filters-v30 enquiries-filter-v30 enquiries-filter-v39 crm-filters-v41" method="get">
        <label>Search
            <input name="q" value="<?= e($filters['q']) ?>" placeholder="Name, phone, company, district, note...">
        </label>
        <label>Status
            <select name="status"><option value="">All status</option><?php foreach($statuses as $key => $label): ?><option value="<?= e($key) ?>" <?= $filters['status']===$key?'selected':'' ?>><?= e($label) ?></option><?php endforeach; ?></select>
        </label>
        <label>District
            <select name="district"><option value="">All districts</option><?php foreach($districts as $d): ?><option value="<?= e($d) ?>" <?= $filters['district']===$d?'selected':'' ?>><?= e($d) ?></option><?php endforeach; ?></select>
        </label>
        <label>Business Type
            <select name="business_type"><option value="">All business types</option><?php foreach($businessTypes as $b): ?><option value="<?= e($b) ?>" <?= $filters['business_type']===$b?'selected':'' ?>><?= e($b) ?></option><?php endforeach; ?></select>
        </label>
        <label>Brand Interest
            <select name="interested_brand"><option value="">All brands</option><?php foreach($brands as $b): ?><option value="<?= e($b) ?>" <?= $filters['interested_brand']===$b?'selected':'' ?>><?= e($b) ?></option><?php endforeach; ?></select>
        </label>
        <label>Enquiry Type
            <select name="enquiry_type"><option value="">All types</option><?php foreach($types as $type): ?><option value="<?= e($type) ?>" <?= $filters['enquiry_type']===$type?'selected':'' ?>><?= e($type) ?></option><?php endforeach; ?></select>
        </label>
        <label>Product
            <select name="product_slug"><option value="">All products</option><?php foreach($productSlugs as $slug): ?><option value="<?= e($slug) ?>" <?= $filters['product_slug']===$slug?'selected':'' ?>><?= e($slug) ?></option><?php endforeach; ?></select>
        </label>
        <label>Lead Source
            <select name="lead_source"><option value="">All sources</option><?php foreach($leadSources as $src): ?><option value="<?= e($src) ?>" <?= $filters['lead_source']===$src?'selected':'' ?>><?= e($src) ?></option><?php endforeach; ?></select>
        </label>
        <label>Follow-up
            <select name="follow"><option value="">All</option><option value="due" <?= $filters['follow']==='due'?'selected':'' ?>>Due today/overdue</option><option value="upcoming" <?= $filters['follow']==='upcoming'?'selected':'' ?>>Upcoming (<?= $upcomingCount ?>)</option></select>
        </label>
        <label>Duplicate
            <select name="duplicate"><option value="">All phones</option><option value="yes" <?= $filters['duplicate']==='yes'?'selected':'' ?>>Duplicate phones only</option></select>
        </label>
        <div class="filter-actions-v30"><button class="button" type="submit">Filter Leads</button><a class="button ghost" href="enquiries.php">Reset</a><a class="button secondary" href="<?= e($exportUrl) ?>">Export CSV</a></div>
    </form>
</section>

<section class="lead-board-v30 lead-board-v39 lead-list-v41">
<?php foreach($rows as $r):
    $st = (string)($r['status'] ?? 'new');
    if(!array_key_exists($st,$statuses)) $st='new';
    $phoneKey = (string)($r['phone_normalized'] ?? normalize_bd_phone($r['phone'] ?? ''));
    $duplicateCount = $phoneKey !== '' ? (int)($duplicateLookup[$phoneKey] ?? enquiry_duplicate_count($pdo, $phoneKey)) : 0;
    $leadNotes = $noteHistory[(int)$r['id']] ?? [];
?>
    <article class="panel lead-card-v30 lead-card-v39 lead-card-v41 status-<?= e($st) ?>" id="lead-<?= (int)$r['id'] ?>">
        <div class="lead-card-head-v30">
            <div>
                <strong><?= e($r['name']) ?></strong>
                <small><?= e($r['district']) ?> · <?= e($r['business_type']) ?> · <?= e($r['interested_brand'] ?? 'Both') ?> · <?= e($r['enquiry_type'] ?? 'Distributor') ?></small>
            </div>
            <span class="status-pill-v30 <?= e($st) ?>"><?= e(enquiry_status_label($st)) ?></span>
        </div>
        <div class="lead-meta-v39">
            <?php if(!empty($r['company_name'])): ?><span><b>Company</b><?= e($r['company_name']) ?></span><?php endif; ?>
            <?php if(!empty($r['business_address'])): ?><span><b>Address</b><?= e($r['business_address']) ?></span><?php endif; ?>
            <?php if(!empty($r['product_name'])): ?><span><b>Product</b><?= e($r['product_name']) ?></span><?php endif; ?>
            <span><b>Type</b><?= e($r['enquiry_type'] ?? 'Distributor') ?></span>
            <span><b>Source</b><?= e($r['lead_source'] ?? 'Website') ?></span>
            <?php if(!empty($r['follow_up_at'])): ?><span class="follow"><b>Follow-up</b><?= e((string)$r['follow_up_at']) ?></span><?php endif; ?>
            <?php if($duplicateCount > 1): ?><a class="duplicate" href="enquiries.php?q=<?= e(urlencode($phoneKey)) ?>&duplicate=yes"><b>Duplicate</b><?= (int)$duplicateCount ?> matching leads</a><?php endif; ?>
        </div>
        <div class="lead-contact-v30">
            <a href="<?= e(tel_url($r['phone'])) ?>">☎ <?= e($r['phone']) ?></a>
            <a href="<?= e(whatsapp_url($r['phone'], lead_whatsapp_message($r))) ?>" target="_blank" rel="noopener">WhatsApp →</a>
        </div>
        <?php if(trim((string)$r['message']) !== ''): ?><p class="lead-message-v30"><?= e($r['message']) ?></p><?php endif; ?>
        <form method="post" class="lead-update-v30 crm-update-v41"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$r['id'] ?>"><input type="hidden" name="action" value="update">
            <label>Status<select name="status"><?php foreach($statuses as $key => $label): ?><option value="<?= e($key) ?>" <?= $st===$key?'selected':'' ?>><?= e($label) ?></option><?php endforeach; ?></select></label>
            <label>Follow-up Date<input type="date" name="follow_up_at" value="<?= e((string)($r['follow_up_at'] ?? '')) ?>"></label>
            <label class="full">Lead Summary<textarea name="admin_note" rows="2" placeholder="Short current summary..."><?= e($r['admin_note'] ?? '') ?></textarea></label>
            <label class="full">Add Follow-up Note<textarea name="note_body" rows="3" placeholder="Call result, customer response, next step..."></textarea></label>
            <div class="lead-actions-v30"><button class="button secondary" type="submit">Save CRM Update</button></div>
        </form>
        <div class="crm-notes-v41">
            <b>Notes History <span><?= count($leadNotes) ?></span></b>
            <?php foreach($leadNotes as $note): ?>
                <div><small><?= e((string)$note['created_at']) ?> · <?= e((string)($note['created_by_name'] ?? 'Admin')) ?><?php if(!empty($note['status_to'])): ?> · <?= e(enquiry_status_label($note['status_to'])) ?><?php endif; ?></small><p><?= e((string)$note['note']) ?></p></div>
            <?php endforeach; ?>
            <?php if(!$leadNotes): ?><em>No saved notes yet.</em><?php endif; ?>
        </div>
        <form method="post" class="lead-delete-v39" data-confirm-form="Delete enquiry and its note history?"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$r['id'] ?>"><input type="hidden" name="action" value="delete"><button class="danger" type="submit">Delete</button></form>
        <small class="lead-date-v30">Submitted: <?= e((string)$r['created_at']) ?><?= !empty($r['handled_at']) ? ' · Handled: ' . e((string)$r['handled_at']) : '' ?><?= !empty($r['updated_at']) ? ' · Updated: ' . e((string)$r['updated_at']) : '' ?></small>
    </article>
<?php endforeach; ?>
<?php if(!$rows): ?><div class="panel empty-state-v30">No enquiries matched your filters.</div><?php endif; ?>
</section>
<?php admin_footer(); ?>
