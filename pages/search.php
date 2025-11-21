<?php
require_once "../api/db.php";

$keyword = isset($_GET['q']) ? trim($_GET['q']) : "";
$sort = $_GET['sort'] ?? "";
$platform = $_GET['platform'] ?? "";
$category = $_GET['category'] ?? "";

// Lấy danh sách nền tảng
$platforms = $conn->query("SELECT DISTINCT code FROM platforms");

// Lấy danh sách normalized_key (dùng làm category tạm)
$categories = $conn->query("SELECT DISTINCT normalized_key FROM products");

// Base SQL
$sql = "SELECT * FROM products WHERE 1";

// Tìm kiếm
if ($keyword !== "") {
    $keyword_safe = $conn->real_escape_string($keyword);
    $sql .= " AND title LIKE '%$keyword_safe%'";
}

// Lọc platform
if ($platform !== "") {
    $platform_safe = $conn->real_escape_string($platform);
    $sql .= " AND platform_id = (SELECT id FROM platforms WHERE code='$platform_safe')";
}

// Lọc category
if ($category !== "") {
    $category_safe = $conn->real_escape_string($category);
    $sql .= " AND normalized_key='$category_safe'";
}

// Sort
if ($sort === "price_asc") {
    $sql .= " ORDER BY price_current ASC";
} 
elseif ($sort === "price_desc") {
    $sql .= " ORDER BY price_current DESC";
} 
elseif ($sort === "name_asc") {
    $sql .= " ORDER BY title ASC";
} 
elseif ($sort === "name_desc") {
    $sql .= " ORDER BY title DESC";
}

$result = $conn->query($sql);
?>

<h1>Search & Filter Products</h1>

<form method="GET" style="margin-bottom:20px; display:flex; gap:10px; flex-wrap:wrap;">

    <!-- Search -->
    <input type="text" name="q" placeholder="Search..." 
           value="<?= htmlspecialchars($keyword) ?>"
           style="padding:6px; min-width:250px;">

    <!-- Platform Filter -->
    <select name="platform" style="padding:6px;">
        <option value="">All Platforms</option>
        <?php while ($p = $platforms->fetch_assoc()): ?>
            <option value="<?= $p['code'] ?>" 
                <?= $platform == $p['code'] ? "selected" : "" ?>>
                <?= strtoupper($p['code']) ?>
            </option>
        <?php endwhile; ?>
    </select>

    <!-- Category Filter -->
    <select name="category" style="padding:6px;">
        <option value="">All Categories</option>
        <?php while ($c = $categories->fetch_assoc()): ?>
            <option value="<?= $c['normalized_key'] ?>" 
                <?= $category == $c['normalized_key'] ? "selected" : "" ?>>
                <?= $c['normalized_key'] ?>
            </option>
        <?php endwhile; ?>
    </select>

    <!-- Sort -->
    <select name="sort" style="padding:6px;">
        <option value="">No Sort</option>
        <option value="price_asc" <?= $sort=="price_asc" ? "selected" : "" ?>>Price ↑</option>
        <option value="price_desc" <?= $sort=="price_desc" ? "selected" : "" ?>>Price ↓</option>
        <option value="name_asc" <?= $sort=="name_asc" ? "selected" : "" ?>>Name A → Z</option>
        <option value="name_desc" <?= $sort=="name_desc" ? "selected" : "" ?>>Name Z → A</option>
    </select>

    <button type="submit" style="padding:6px 15px;">Filter</button>
</form>

<hr>

<?php if ($result->num_rows > 0): ?>
    <?php while($row = $result->fetch_assoc()): ?>
        <div style="margin-bottom: 20px; display:flex; gap:15px; align-items:center; border-bottom:1px solid #eee; padding-bottom:15px;">
            
            <!-- Image -->
            <img src="<?= $row['image_url'] ?: 'https://via.placeholder.com/120?text=No+Image' ?>"
                 style="width:120px; border:1px solid #ccc;">

            <div style="flex:1;">
                <strong><?= $row['title'] ?></strong><br>

                <span style="color:#e91e63; font-weight:bold;">
                    <?= number_format($row['price_current']) ?>đ
                </span><br>

                <small>Platform: <?= $row['platform_id'] ?></small><br>

                <a href="<?= $row['product_url'] ?>" target="_blank" style="color:#2196f3;">View</a>
            </div>

            <!-- Compare Checkbox -->
            <div>
                <label style="display:flex; align-items:center;">
                    <input type="checkbox" class="cmp-box" value="<?= $row['id'] ?>">
                    <span style="margin-left:5px;">Compare</span>
                </label>
            </div>

        </div>
    <?php endwhile; ?>
<?php else: ?>
    <p>No products found.</p>
<?php endif; ?>


<!-- Compare Bar -->
<div id="compareBar"
     style="
        display:none; position:fixed; bottom:20px; left:50%; transform:translateX(-50%);
        background:#ff5722; padding:12px 25px; border-radius:30px; color:white;
        box-shadow:0 4px 12px rgba(0,0,0,0.25);
     ">
    <span id="cmpCount">0</span> selected —
    <a id="cmpBtn" href="#" style="color:white; font-weight:bold; text-decoration:underline;">Compare now</a>
</div>

<script>
let selected = [];

document.querySelectorAll(".cmp-box").forEach(box => {
    box.addEventListener("change", function() {
        let id = this.value;

        if (this.checked) {
            if (!selected.includes(id)) selected.push(id);
        } else {
            selected = selected.filter(x => x != id);
        }

        updateCompareBar();
    });
});

function updateCompareBar() {
    const bar = document.getElementById("compareBar");
    const count = document.getElementById("cmpCount");
    const btn = document.getElementById("cmpBtn");

    if (selected.length === 0) {
        bar.style.display = "none";
        return;
    }

    if (selected.length > 4) {
        alert("Maximum 4 products allowed!");
        selected.pop();
    }

    bar.style.display = "block";
    count.textContent = selected.length;
    btn.href = "../pages/compare.php?ids=" + selected.join(",");
}
</script>
