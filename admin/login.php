<?php session_start(); 
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in']) {
    header("Location: admin_dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Login</title>
<link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>

<div class="login-container">
    <h2>Admin Login</h2>

    <form id="loginForm">
        <input type="text" id="username" placeholder="Username" required>
        <input type="password" id="password" placeholder="Password" required>

        <button type="submit">Login</button>
        <p id="msg" class="msg"></p>
    </form>
</div>

<script>
document.getElementById("loginForm").addEventListener("submit", async (e) => {
    e.preventDefault();
    let username = document.getElementById("username").value;
    let password = document.getElementById("password").value;

    let res = await fetch("../api/auth/admin_login.php", {
        method: "POST",
        headers: {"Content-Type": "application/x-www-form-urlencoded"},
        body: `username=${username}&password=${password}`
    });
    let data = await res.json();

    if (data.status === "success") {
        window.location.href = "admin_dashboard.php";
    } else {
        document.getElementById("msg").innerText = data.message;
    }
});
</script>

</body>
</html>
