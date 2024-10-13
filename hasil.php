<?php
session_start();

// Cek login
if (!isset($_SESSION['login'])) {
    header('Location: login.php');
    exit;
}

require 'db_connect.php'; 

// Ambil data sewa dari database
$currentUser = $_SESSION['login'];
$sql = "SELECT ps_list.name FROM sewa 
        INNER JOIN ps_list ON sewa.ps_id = ps_list.id
        WHERE sewa.user_id = (SELECT id FROM user WHERE username = ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $currentUser);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Sewa</title>
</head>
<body>
    <h1>Hasil Sewa Anda</h1>
    
    <h2>Barang yang Disewa</h2>
    <ul>
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<li>" . htmlspecialchars($row['name']) . "</li>";
            }
        } else {
            echo "<li>Belum ada barang yang disewa.</li>";
        }
        ?>
    </ul>

    <form action="logout.php" method="post">
        <button type="submit">Logout</button>
    </form>
</body>
</html>
