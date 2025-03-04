<?php
require 'koneksi.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: dashboard.php');
    exit();
}

$id = $_GET['id'];

// Menggunakan prepared statement untuk mencegah SQL Injection
$stmt = $conn->prepare("DELETE FROM buku WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    $stmt->close();
    header('Location: dashboard.php');
    exit();
} else {
    echo "Gagal menghapus buku: " . htmlspecialchars($conn->error);
}

$stmt->close();
?>
