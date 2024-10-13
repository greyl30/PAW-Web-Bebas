<?php
session_start();
require 'db_connect.php';

// Cek login
if (isset($_SESSION['login'])) {
    header('location: sewa.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Register
    if (isset($_POST['register'])) {
        $stmt = $conn->prepare("INSERT INTO user (username, password) VALUES (?, ?)");
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt->bind_param("ss", $username, $hashedPassword);
        $stmt->execute();
        echo "Register berhasil. Silakan Login";
        $stmt->close();
    }

    // Login tanpa register
    if (isset($_POST['login'])) {
        $stmt = $conn->prepare("SELECT * FROM user WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['login'] = $username;
            header('location: sewa.php');
            exit;
        } else {
            echo "Anda belum melakukan registrasi. Mohon registrasi terlebih dahulu.";
        }
        $stmt->close();
    }
}
?>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
</head>
<body>
    <h1>Login</h1>
    <form method="post">
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit" name="login">Login</button>
        <button type="submit" name="register">Register</button>
    </form>
</body>
</html>
