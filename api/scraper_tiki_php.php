<?php
header('Content-Type: application/json; charset=utf-8');

// Hàm lấy dữ liệu từ URL (thay thế requests.get của Python)
function fetch_url($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)');
    // Tiki có thể chặn nếu thiếu User-Agent chuẩn
    $output = curl_exec($ch);
    curl_close($ch);
    return $output;
}

// Lấy URL từ tham số ?url=...
$input_url = $_GET['url'] ?? '';
if (!$input_url) die(json_encode(["error" => "Missing URL"]));

// Lấy Category ID từ URL (Logic regex giống Python)
if (preg_match('/\/c(\d+)/', $input_url, $matches)) {
    $category_id = $matches[1];
} else {
    die(json_encode(["error" => "Invalid Tiki URL"]));
}

$results = [];
$page = 1;
$max_pages = 2; // Giới hạn trang để demo, bạn có thể tăng lên

while ($page <= $max_pages) {
    $api_url = "https://tiki.vn/api/personalish/v1/blocks/listings?category=$category_id&page=$page";
    
    $json = fetch_url($api_url);
    $data = json_decode($json, true);
    
    $items = $data['data'] ?? [];
    if (empty($items)) break;

    foreach ($items as $p) {
        $sold = $p['quantity_sold']['value'] ?? null;
        
        $results[] = [
            "title" => $p['name'] ?? '',
            "link" => "https://tiki.vn/" . ($p['url_path'] ?? ''),
            "image" => $p['thumbnail_url'] ?? '',
            "price_new" => $p['price'] ?? 0,
            "price_old" => $p['original_price'] ?? 0,
            "discount" => $p['discount_rate'] ?? 0,
            "rating" => $p['rating_average'] ?? 0,
            "rating_count" => $p['review_count'] ?? 0,
            "sold" => $sold
        ];
    }
    $page++;
}

// Trả về JSON y hệt như Python làm
echo json_encode($results, JSON_UNESCAPED_UNICODE);
?>