<?php
require_once "../api/db.php";
session_start();

// Kiểm tra login admin
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id'])) {
    die("Missing product ID");
}

$id = intval($_GET['id']);

// Lấy thông tin sản phẩm
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

if (!$product) {
    die("Product not found");
}

// Nếu admin bấm Lưu
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $title = $_POST["title"];
    $price = intval($_POST["price"]);
    $shop = $_POST["shop_name"];
    $url  = $_POST["product_url"];

    $update = $conn->prepare("UPDATE products 
        SET title=?, price_current=?, shop_name=?, product_url=?, updated_at=NOW() 
        WHERE id=?");

    $update->bind_param("sissi", $title, $price, $shop, $url, $id);
    $update->execute();

    header("Location: index.php?msg=updated");
    exit;
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Product</title>
    <style>
        body { font-family: Inter, sans-serif; margin: 40px; }
        .form-box { width: 450px; padding: 20px; border: 1px solid #ccc; background: #fafafa; }
        input[type=text], input[type=number] {
            width: 100%; padding: 8px; margin: 10px 0;
        }
        button { padding: 10px 18px; background: black; color: white; border: none; cursor: pointer; }
    </style>
</head>
<body>

<h2>Edit Product</h2>

<div class="form-box">
<form method="POST">

    <label>Product Title</label>
    <input type="text" name="title" value="<?= htmlspecialchars($product['title']) ?>">

    <label>Price (current)</label>
    <input type="number" name="price" value="<?= $product['price_current'] ?>">

    <label>Shop Name</label>
    <input type="text" name="shop_name" value="<?= htmlspecialchars($product['shop_name']) ?>">

    <label>Product URL</label>
    <input type="text" name="product_url" value="<?= htmlspecialchars($product['product_url']) ?>">

    <button type="submit">Save Changes</button>

</form>
</div>

</body>
</html>
