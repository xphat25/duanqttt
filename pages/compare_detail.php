<?php
include 'db.php';
$key = $_GET['key'];

$res = $conn->query("
SELECT p.*, pl.name AS platform
FROM products p
JOIN platforms pl ON p.platform_id = pl.id
WHERE normalized_key = '$key'
ORDER BY price_current ASC
");
?>

<h2>Chi tiết so sánh: <?= $key ?></h2>

<table border="1" cellpadding="8">
<tr>
    <th>Ảnh</th>
    <th>Tên</th>
    <th>Nền tảng</th>
    <th>Giá</th>
    <th>Shop</th>
    <th>Link</th>
</tr>

<?php while ($row = $res->fetch_assoc()) { ?>
<tr>
    <td><img src="<?= $row['image_url'] ?>" width="60"></td>
    <td><?= $row['title'] ?></td>
    <td><?= $row['platform'] ?></td>
    <td><?= number_format($row['price_current']) ?></td>
    <td><?= $row['shop_name'] ?></td>
    <td><a href="<?= $row['product_url'] ?>" target="_blank">Mở</a></td>
</tr>
<?php } ?>
</table>

