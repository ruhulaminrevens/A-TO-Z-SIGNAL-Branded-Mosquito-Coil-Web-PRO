<?php
require_once __DIR__ . '/_layout.php';
require_admin();
ensure_phase2_cms_schema();
if (!hash_equals($_SESSION['csrf_token'] ?? '', $_GET['token'] ?? '')) exit('Invalid token');
$id=(int)($_GET['id'] ?? 0);
if($id){ $stmt=db()->prepare('DELETE FROM company_timeline WHERE id=?'); $stmt->execute([$id]); }
header('Location: timeline.php');
exit;
