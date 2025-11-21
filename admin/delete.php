<?php
require_once "../api/db.php";
session_start();

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id'])) {
    die("Missing ID");
}

$id = intval($_GET['id']);

// Xóa snapshots trước
$conn->query("DELETE FROM product_snapshots WHERE product_id = $id");

// Xóa sản phẩm
$conn->query("DELETE FROM products WHERE id = $id");

header("Location: index.php?msg=deleted");
exit;
?>
