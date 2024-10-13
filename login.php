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
            $stmt = $pdo->prepare("INSERT INTO user (username, password) VALUES (:username, :password)");
            $stmt->execute(['username' => $username, 'password' => password_hash($password, PASSWORD_DEFAULT)]);
            echo "Register berhasil. Silakan Login";
        }

        // Login tanpa register
        if (isset($_POST['login'])) {
            $stmt = $pdo->prepare("SELECT * FROM user WHERE username = :username");
            $stmt->execute(['username' => $username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['login'] = $username;
                header('location: sewa.php');
                exit;
            } else {
                echo "Username atau password salah, atau Anda belum terdaftar.";
            }
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
