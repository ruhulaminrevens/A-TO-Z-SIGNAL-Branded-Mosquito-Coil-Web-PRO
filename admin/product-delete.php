<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin();
if (!hash_equals($_SESSION['csrf_token'] ?? '', $_GET['token'] ?? '')) exit('Invalid token');
$id=(int)($_GET['id'] ?? 0);
if ($id) { $stmt=db()->prepare('DELETE FROM products WHERE id=?'); $stmt->execute([$id]); }
header('Location: products.php');
exit;
