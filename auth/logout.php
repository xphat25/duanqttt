<?php
session_start();
// Hủy session của user
unset($_SESSION['user_logged_in']);
unset($_SESSION['username']);
unset($_SESSION['user_id']);

// Chuyển hướng về trang chủ hoặc trang đăng nhập
header("Location: ../index.php");
exit;
?>