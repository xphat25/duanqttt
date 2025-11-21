<?php
require_once __DIR__ . "/../api/db.php";

// Lấy tham số tìm kiếm, lọc và sắp xếp
$keyword = isset($_GET['q']) ? trim($_GET['q']) : "";
$sort = $_GET['sort'] ?? "";
$platform = $_GET['platform'] ?? "";
$category = $_GET['category'] ?? "";

// Lấy danh sách nền tảng và danh mục để hiển thị trong bộ lọc
$platforms = $conn->query("SELECT DISTINCT code, name FROM platforms");
$categories = $conn->query("SELECT DISTINCT normalized_key FROM products WHERE normalized_key IS NOT NULL AND normalized_key != ''");

// Base SQL
$sql = "SELECT p.*, pf.name AS platform_name 
        FROM products p
        LEFT JOIN platforms pf ON p.platform_id = pf.id
        WHERE 1";

// Tìm kiếm theo từ khóa
if ($keyword !== "") {
    $keyword_safe = $conn->real_escape_string($keyword);
    $sql .= " AND p.title LIKE '%$keyword_safe%'";
}

// Lọc theo nền tảng
if ($platform !== "") {
    $platform_safe = $conn->real_escape_string($platform);
    $sql .= " AND pf.code = '$platform_safe'";
}

// Lọc theo danh mục
if ($category !== "") {
    $category_safe = $conn->real_escape_string($category);
    $sql .= " AND p.normalized_key = '$category_safe'";
}

// Sắp xếp
if ($sort === "price_asc") {
    $sql .= " ORDER BY p.price_current ASC";
} 
elseif ($sort === "price_desc") {
    $sql .= " ORDER BY p.price_current DESC";
} 
elseif ($sort === "name_asc") {
    $sql .= " ORDER BY p.title ASC";
} 
elseif ($sort === "name_desc") {
    $sql .= " ORDER BY p.title DESC";
} else {
    $sql .= " ORDER BY p.id DESC"; // Mặc định sắp xếp theo ID mới nhất
}


$result = $conn->query($sql);
$product_list = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $product_list[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Products List</title>
    <link rel="stylesheet" href="../style.css"> 
    <style>
        /* Tùy chỉnh nhỏ để form trông giống các input group khác */
        .search-form-group {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }
        .search-form-group .form-input, .search-form-group select, .search-form-group button {
            padding: 8px 12px;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            font-size: 14px;
            outline: none;
            transition: border 0.2s;
        }
        .search-form-group .form-input { flex: 1; min-width: 200px; }
        .product-grid-empty {
            text-align: center;
            grid-column: 1 / -1;
            padding: 40px 0;
            color: var(--text-muted);
        }
    </style>
</head>
<body>

<main class="main-content" style="margin-top: 0; padding: 0;">
    <h1 class="hero-title" style="font-size: 24px;">Saved Products & Search</h1>
    <p class="hero-description" style="margin-bottom: 20px;">
        <span class="badge"><?= count($product_list) ?></span> products found.
    </p>

    <form method="GET" class="search-form-group">

        <input type="text" name="q" placeholder="Search product title..." 
               value="<?= htmlspecialchars($keyword) ?>"
               class="form-input">

        <select name="platform">
            <option value="">All Platforms</option>
            <?php 
            if ($platforms->num_rows > 0) {
                $platforms->data_seek(0);
                while ($p = $platforms->fetch_assoc()): ?>
                    <option value="<?= $p['code'] ?>" 
                        <?= $platform == $p['code'] ? "selected" : "" ?>>
                        <?= htmlspecialchars($p['name']) ?>
                    </option>
                <?php endwhile; 
            }
            ?>
        </select>

        <select name="category">
            <option value="">All Categories</option>
            <?php 
            if ($categories->num_rows > 0) {
                $categories->data_seek(0);
                while ($c = $categories->fetch_assoc()): ?>
                    <option value="<?= $c['normalized_key'] ?>" 
                        <?= $category == $c['normalized_key'] ? "selected" : "" ?>>
                        <?= ucwords(str_replace('-', ' ', $c['normalized_key'])) ?>
                    </option>
                <?php endwhile; 
            }
            ?>
        </select>

        <select name="sort">
            <option value="">Sort By...</option>
            <option value="price_asc" <?= $sort=="price_asc" ? "selected" : "" ?>>Price ↑</option>
            <option value="price_desc" <?= $sort=="price_desc" ? "selected" : "" ?>>Price ↓</option>
            <option value="name_asc" <?= $sort=="name_asc" ? "selected" : "" ?>>Name A → Z</option>
            <option value="name_desc" <?= $sort=="name_desc" ? "selected" : "" ?>>Name Z → A</option>
        </select>

        <button type="submit" class="btn btn-primary">Filter / Search</button>
        <?php if ($keyword || $platform || $category || $sort): ?>
            <button type="button" class="btn" onclick="window.location.href='products.php'">Reset</button>
        <?php endif; ?>
    </form>
    
    <div class="product-grid">
        <?php if (count($product_list) > 0): ?>
            <?php foreach ($product_list as $row): ?>
                <div class="product-card">
                    <div class="product-image-container">
                        <img src="<?= htmlspecialchars($row['image_url']) ?: 'https://placehold.co/300x300?text=No+Image' ?>"
                             class="product-image"
                             onerror="this.src='https://placehold.co/300x300?text=No+Image'">
                    </div>

                    <div class="product-info">
                        <h3 class="product-title"><?= htmlspecialchars($row['title']) ?></h3>

                        <div class="price-box">
                            <span class="current-price"><?= number_format($row['price_current']) ?>₫</span>
                            <?php if ($row['price_original']): ?>
                                <span class="old-price"><?= number_format($row['price_original']) ?>₫</span>
                            <?php endif; ?>
                        </div>
                        <p style="font-size: 11px; color: var(--text-muted); margin-top: 5px;">
                            Platform: <?= htmlspecialchars($row['platform_name']) ?>
                            <?php if ($row['sold_quantity'] !== null): ?>
                                | Sold: <?= number_format($row['sold_quantity']) ?>
                            <?php endif; ?>
                        </p>
                    </div>

                    <a href="<?= htmlspecialchars($row['product_url']) ?>" target="_blank" class="view-btn">View Product</a>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="product-grid-empty">No products found matching your criteria.</p>
        <?php endif; ?>
    </div>
</main>

</body>
</html>