<?php
session_start();
require_once "../db.php";

header('Content-Type: application/json; charset=utf-8');

if (!isset($_POST['username']) || !isset($_POST['password'])) {
    echo json_encode(["status" => "error", "message" => "Missing registration data"]);
    exit;
}

$user = trim($_POST['username']);
$pass = $_POST['password'];

if (strlen($user) < 3 || strlen($pass) < 6) {
    echo json_encode(["status" => "error", "message" => "Username must be at least 3 characters and password at least 6 characters."]);
    exit;
}

// Kiểm tra xem username đã tồn tại chưa
$stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
$stmt->bind_param("s", $user);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->close();
    echo json_encode(["status" => "error", "message" => "Username already exists."]);
    exit;
}
$stmt->close();

// Hash mật khẩu
$password_hash = password_hash($pass, PASSWORD_DEFAULT);

// Thêm người dùng mới
$stmt = $conn->prepare("INSERT INTO users (username, password_hash) VALUES (?, ?)");
$stmt->bind_param("ss", $user, $password_hash);

if ($stmt->execute()) {
    // Tự động đăng nhập sau khi đăng ký
    $_SESSION['user_logged_in'] = true;
    $_SESSION['username'] = $user;
    $_SESSION['user_id'] = $conn->insert_id;

    echo json_encode(["status" => "success"]);
} else {
    echo json_encode(["status" => "error", "message" => "Registration failed: " . $conn->error]);
}

$stmt->close();
?>