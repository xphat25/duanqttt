<?php
session_start();
require_once "../db.php";

if (!isset($_POST['username']) || !isset($_POST['password'])) {
    echo json_encode(["status" => "error", "message" => "Missing login data"]);
    exit;
}

$user = $_POST['username'];
$pass = $_POST['password'];

// Tài khoản admin cố định (có thể chuyển sang DB)
$ADMIN_USER = "admin";
$ADMIN_PASS = "123456";

if ($user === $ADMIN_USER && $pass === $ADMIN_PASS) {
    $_SESSION['admin_logged_in'] = true;
    echo json_encode(["status" => "success"]);
} else {
    echo json_encode(["status" => "error", "message" => "Invalid credentials"]);
}
?>
