<?php
require_once "../api/db.php";

// Lấy list id từ GET
$ids = isset($_GET['ids']) ? $_GET['ids'] : [];
if (!is_array($ids)) $ids = explode(",", $ids);

$ids_clean = array_map('intval', $ids);
$id_list = implode(",", $ids_clean);

// Lấy sản phẩm
$products = [];
if ($id_list) {
    $query = $conn->query("
        SELECT p.*, pf.name AS platform_name
        FROM products p
        LEFT JOIN platforms pf ON p.platform_id = pf.id
        WHERE p.id IN ($id_list)
    ");
    while ($row = $query->fetch_assoc()) {
        $products[] = $row;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Compare Products</title>

<style>
    body {
        font-family: Arial;
        background: #f7f7f7;
        padding: 20px;
    }
    
    h1 {
        text-align: center;
        font-size: 32px;
        margin-bottom: 25px;
    }

    .compare-table {
        width: 100%;
        border-collapse: collapse;
        background: white;
        box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        border-radius: 12px;
        overflow: hidden;
    }

    th {
        background: #ff5722;
        color: white;
        padding: 14px;
        font-size: 18px;
        text-align: center;
    }

    td {
        padding: 12px;
        text-align: center;
        border-bottom: 1px solid #eee;
    }

    .p-img {
        width: 150px;
        height: 150px;
        object-fit: contain;
        border-radius: 10px;
        background: #fff;
    }

    .title {
        font-size: 16px;
        font-weight: bold;
        line-height: 1.3;
    }

    .price-new {
        color: #e91e63;
        font-size: 20px;
        font-weight: bold;
    }

    .price-old {
        text-decoration: line-through;
        color: #9e9e9e;
        font-size: 14px;
    }

    .btn-view {
        background: #2196f3;
        color: white;
        padding: 8px 12px;
        border-radius: 6px;
        text-decoration: none;
        display: inline-block;
    }
    .btn-view:hover {
        background: #1976d2;
    }

    .warning {
        color: red;
        text-align: center;
        font-size: 18px;
        margin-top: 20px;
    }
</style>

</head>
<body>

<h1>Product Comparison</h1>

<?php if (count($products) < 2): ?>
<p class="warning">⚠ Please select at least 2 products to compare!</p>
<p style="text-align:center;"><a href="../pages/search.php">← Return to Search</a></p>
<?php exit; endif; ?>

<table class="compare-table">

    <!-- HEADER -->
    <tr>
        <th>Feature</th>
        <?php foreach ($products as $p): ?>
        <th><?= htmlspecialchars($p["title"]) ?></th>
        <?php endforeach; ?>
    </tr>

    <!-- IMAGE -->
    <tr>
        <td>Image</td>
        <?php foreach ($products as $p): ?>
        <td>
            <img class="p-img"
                 src="<?= $p['image_url'] ?: 'https://placehold.co/200x200?text=No+Image' ?>"
                 onerror="this.src='https://placehold.co/200x200?text=No+Image'">
        </td>
        <?php endforeach; ?>
    </tr>

    <!-- PRICE -->
    <tr>
        <td>Price</td>
        <?php foreach ($products as $p): ?>
        <td>
            <div class="price-new"><?= number_format($p['price_current']) ?>đ</div>
            <?php if ($p['price_original']): ?>
            <div class="price-old"><?= number_format($p['price_original']) ?>đ</div>
            <?php endif; ?>
        </td>
        <?php endforeach; ?>
    </tr>

    <!-- RATING -->
    <tr>
        <td>Rating</td>
        <?php foreach ($products as $p): ?>
        <td><?= $p['rating_avg'] ? $p['rating_avg'] . " ⭐" : "N/A" ?></td>
        <?php endforeach; ?>
    </tr>

    <!-- SOLD -->
    <tr>
        <td>Sold</td>
        <?php foreach ($products as $p): ?>
        <td><?= $p['sold_quantity'] ?: 0 ?></td>
        <?php endforeach; ?>
    </tr>

    <!-- PLATFORM -->
    <tr>
        <td>Platform</td>
        <?php foreach ($products as $p): ?>
        <td><?= $p['platform_name'] ?: "Unknown" ?></td>
        <?php endforeach; ?>
    </tr>

    <!-- LINK -->
    <tr>
        <td>Link</td>
        <?php foreach ($products as $p): ?>
        <td><a class="btn-view" href="<?= $p['product_url'] ?>" target="_blank">Open</a></td>
        <?php endforeach; ?>
    </tr>

</table>

</body>
</html>
