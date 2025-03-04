<?php
session_start();

if (isset($_GET['confirm']) && $_GET['confirm'] === 'yes') {
    // Menghapus semua data sesi
    session_unset();
    session_destroy();

    // Redirect ke halaman login atau halaman utama
    header('Location: index.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logout</title>
    <script>
        function confirmLogout() {
            let confirmAction = confirm("Yakin ingin keluar?");
            if (confirmAction) {
                window.location.href = "logout.php?confirm=yes";
            }
        }
    </script>
</head>
<body>
    <button onclick="confirmLogout()">Logout</button>
</body>
</html>
