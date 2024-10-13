<?php
    session_start();
    require 'db_connect.php';

    // Cek login
    if (!isset($_SESSION['login'])) {
        header('Location: login.php');
        exit;
    }

    $currentUser = $_SESSION['login'];

    // Ambil daftar PlayStation dari database
    $stmt = $pdo->query("SELECT * FROM ps_list");
    $psList = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Ambil data sewa dari database
    $stmt = $pdo->prepare("SELECT ps_list.* FROM sewa JOIN ps_list ON sewa.ps_id = ps_list.id WHERE sewa.user_id = (SELECT id FROM user WHERE username = :username)");
    $stmt->execute(['username' => $currentUser]);
    $dataSewa = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Proses sewa
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (isset($_POST['psId'])) {
            $psId = $_POST['psId'];

            // Cek apakah PlayStation tersedia
            $stmt = $pdo->prepare("SELECT * FROM ps_list WHERE id = :id AND status = 'Tersedia'");
            $stmt->execute(['id' => $psId]);
            $ps = $stmt->fetch();

            if ($ps) {
                // Ubah status menjadi 'Booked' dan simpan data sewa
                $stmt = $pdo->prepare("UPDATE ps_list SET status = 'Booked' WHERE id = :id");
                $stmt->execute(['id' => $psId]);

                $stmt = $pdo->prepare("INSERT INTO sewa (user_id, ps_id) VALUES ((SELECT id FROM user WHERE username = :username), :psId)");
                $stmt->execute(['username' => $currentUser, 'psId' => $psId]);

                header("Refresh:0");
                exit; 
            } 
        }

        // Proses batal sewa
        if (isset($_POST['batal'])) {
            $batalId = $_POST['batalId'];

            // Hapus dari tabel sewa dan ubah status PlayStation menjadi 'Tersedia'
            $stmt = $pdo->prepare("DELETE FROM sewa WHERE ps_id = :psId AND user_id = (SELECT id FROM user WHERE username = :username)");
            $stmt->execute(['psId' => $batalId, 'username' => $currentUser]);

            $stmt = $pdo->prepare("UPDATE ps_list SET status = 'Tersedia' WHERE id = :psId");
            $stmt->execute(['psId' => $batalId]);
            header("Refresh:0");
            exit; 
        }
    }

    $detailPs = null;
    if (isset($_GET['id'])) {
        $psId = $_GET['id'];

        // Ambil detail PlayStation dari database
        $stmt = $pdo->prepare("SELECT * FROM ps_list WHERE id = :id");
        $stmt->execute(['id' => $psId]);
        $detailPs = $stmt->fetch(PDO::FETCH_ASSOC);
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sewa PS</title>
    <style>
        .detail-box {
            display: <?= $detailPs ? 'block' : 'none' ?>;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 300px;
            padding: 20px;
            background-color: white;
            border: 1px solid #ccc;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            z-index: 1000;
        }
        .close-button {
            display: block;
            margin-top: 10px;
            text-align: center;
            background-color: grey;
            color: white;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
        }
        .overlay {
            display: <?= $detailPs ? 'block' : 'none' ?>; 
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 500;
        }
    </style>
</head>
<body>
    <h2>Selamat Datang, <?= $_SESSION['login'] ?></h2>

    <h2>Daftar PlayStation</h2>
    <ul>
        <?php foreach ($psList as $ps): ?>
            <li>
                <?= $ps['name'] ?> - <?= $ps['status'] ?>
                <form method="GET" action="sewa.php" style="display:inline;">
                    <input type="hidden" name="id" value="<?= $ps['id'] ?>">
                    <button type="submit">Detail</button>
                </form>
                <?php if ($ps['status'] === 'Tersedia'): ?>
                <form method="post" style="display:inline;">
                    <input type="hidden" name="psId" value="<?= $ps['id'] ?>">
                    <button type="submit">Sewa</button>
                </form>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>

    <h2>Hasil Sewa</h2>
    <ul>
        <?php if (!empty($dataSewa)): ?>
            <?php foreach ($dataSewa as $item): ?>
                <li>
                    <?= $item['name'] ?>
                    <form method="post" style="display:inline;">
                        <input type="hidden" name="batalId" value="<?= $item['id'] ?>">
                        <button type="submit" name="batal">Batal Sewa</button>
                    </form>
                </li>
            <?php endforeach; ?>
        <?php else: ?>
            Belum ada hasil sewa.
        <?php endif; ?>
    </ul>

    <div class="overlay" onclick="location.href='sewa.php';"></div> 
    <div class="detail-box">
        <h2>Detail PlayStation</h2>
        <p>Nama: <?= $detailPs['name'] ?? 'N/A' ?></p>
        <p>Status: <?= $detailPs['status'] ?? 'N/A' ?></p>
        <p>Deskripsi: <?= $detailPs['description'] ?? 'N/A' ?></p> 
        <form method="GET" action="sewa.php">
            <input type="hidden" name="id" value="">
            <button type="button" class="close-button" onclick="location.href='sewa.php';">Tutup</button>
        </form>
    </div>

    <form action="logout.php" method="post">
        <button type="submit">Logout</button>
    </form>
</body>
</html>
