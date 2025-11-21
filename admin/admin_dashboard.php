<?php
session_start();
require_once "../api/db.php";

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

$products = $conn->query("SELECT * FROM products ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Dashboard</title>
<link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>

<header class="admin-header">
    <h2>Admin Dashboard</h2>
    <a href="logout.php" class="logout-btn">Logout</a>
</header>

<div class="admin-container">

<h3>📦 Product Manager</h3>

<table class="product-table">
<tr>
    <th>ID</th>
    <th>Image</th>
    <th>Title</th>
    <th>Price</th>
    <th>Platform</th>
    <th>Actions</th>
</tr>

<?php while ($p = $products->fetch_assoc()) { ?>
<tr>
    <td><?= $p['id'] ?></td>
    <td><img src="<?= $p['image_url'] ?>" width="60"></td>
    <td><?= $p['title'] ?></td>
    <td><?= number_format($p['price_current']) ?> đ</td>
    <td><?= $p['platform_id'] ?></td>
    <td>
        <a href="edit.php?id=<?= $p['id'] ?>" class="btn-edit">Edit</a>
        <a href="delete.php?id=<?= $p['id'] ?>" class="btn-delete" onclick="return confirm('Delete product?');">Delete</a>
    </td>
</tr>
<?php } ?>

</table>

</div>

</body>
</html>
