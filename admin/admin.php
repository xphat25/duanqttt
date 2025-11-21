<?php
require_once "db.php";

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM products WHERE id = $id");
    header("Location: admin.php");
}
$result = $conn->query("SELECT * FROM products ORDER BY id DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Admin</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header class="site-header">
    <div class="header-container">
        <div class="logo-area">
            <div class="logo-dot"></div>
            <h1 class="logo-text">Admin</h1>
        </div>
    </div>
</header>

<main class="main-content">
    <h1 class="hero-title">Product Management</h1>

    <table class="table">
        <tr>
            <th>ID</th>
            <th>Title</th>
            <th>Price</th>
