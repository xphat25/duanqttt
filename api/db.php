<?php
$host = "127.0.0.1";
$user = "root";
$pass = "";
$db   = "thuthapdltm";
$port = 3307;

$conn = new mysqli($host, $user, $pass, $db, $port);
$conn->set_charset("utf8mb4");

if ($conn->connect_error) {
    die("Kết nối MySQL thất bại: " . $conn->connect_error);
}
?>
