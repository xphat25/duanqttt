<?php
$host   = "localhost"; 
$user   = "sql_nhom30_itimi";
$pass   = "71bbaeb8a35948";
$dbname = "sql_nhom30_itimi";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    // Trả về JSON lỗi để Frontend nhận biết
    die(json_encode([
        "status" => "error", 
        "message" => "Không thể kết nối đến Database Online: " . $conn->connect_error
    ]));
}

mysqli_set_charset($conn, "utf8");
?>