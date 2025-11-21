<?php
require_once __DIR__ . "/../api/db.php";
$result = $conn->query("
    SELECT p.*, pf.name AS platform_name 
    FROM products p
    LEFT JOIN platforms pf ON p.platform_id = pf.id
    ORDER BY p.id DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Products List</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header class="site-header">
    <div class="header-container">
        <div class="logo-area">
            <div class="logo-dot"></div>
            <h1 class="logo-text">Products</h1>
        </div>
    </div>
</header>

<main class="main-content">
    <h1 class="hero-title">Stored Products</h1>
    <p class="hero-description">All scraped products stored in MySQL</p>

    <div class="product-grid">
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="product-card">
                <img src="<?= $row['image_url'] ?>" class="product-image">

                <h3 class="product-title"><?= $row['title'] ?></h3>

                <p class="product-price">
                    <strong><?= number_format($row['price_current']) ?>₫</strong>
                    <?php if ($row['price_original']): ?>
                        <span class="old-price"><?= number_format($row['price_original']) ?>₫</span>
                    <?php endif; ?>
                </p>

                <p class="product-shop">Platform: <?= $row['platform_name'] ?></p>

                <a class="btn btn-primary" href="<?= $row['product_url'] ?>" target="_blank">Open Product</a>
            </div>
        <?php endwhile; ?>
    </div>
</main>

</body>
</html>
