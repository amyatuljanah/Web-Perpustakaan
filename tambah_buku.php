<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: index.php');
    exit();
}
require 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $judul = $_POST['judul'];
    $pengarang = $_POST['pengarang'];
    $tahun_terbit = $_POST['tahun_terbit'];

    // Menangani upload foto
    $foto = $_FILES['foto'];

    // Jika ada foto yang diupload
    if ($foto['error'] == 0) {
        // Tentukan nama file foto yang unik
        $fotoNama = uniqid('', true) . '.' . pathinfo($foto['name'], PATHINFO_EXTENSION);
        $fotoPath = 'uploads/' . $fotoNama;

        // Pindahkan file ke folder 'uploads'
        move_uploaded_file($foto['tmp_name'], $fotoPath);
    } else {
        // Jika tidak ada foto, set ke NULL
        $fotoPath = NULL;
    }

    // Mencegah SQL Injection dengan prepared statement
    $stmt = $conn->prepare("INSERT INTO buku (judul, pengarang, tahun_terbit, foto) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $judul, $pengarang, $tahun_terbit, $fotoPath);

   // Eksekusi query dan cek apakah berhasil
if ($stmt->execute()) {
    // Menyimpan pesan sukses dalam session
    session_start(); // Pastikan session sudah dimulai
    $_SESSION['message'] = 'Buku telah berhasil ditambahkan';

    // Redirect ke dashboard
    header('Location: dashboard.php');
    exit();
} else {
    $error = "Gagal menambahkan buku: " . $conn->error;
}

$stmt->close();

}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Tambah Buku</title>
    <link rel="stylesheet" href="tambah_buku.css">
</head>
<body>
    <h1>Tambah Buku</h1>
    <?php if (isset($error)): ?>
        <p style="color:red;"><?= htmlspecialchars($error); ?></p>
    <?php endif; ?>
    <!-- Form untuk menambah buku -->
    <form action="tambah_buku.php" method="post" enctype="multipart/form-data">
        <label for="judul">Judul:</label>
        <input type="text" name="judul" required><br>

        <label for="pengarang">Pengarang:</label>
        <input type="text" name="pengarang" required><br>

        <label for="tahun_terbit">Tahun Terbit:</label>
        <input type="number" name="tahun_terbit" required><br>

        <label for="foto">Foto Buku:</label>
        <input type="file" name="foto" accept="image/*"><br>

        <button type="submit" name="submit">Tambah Buku</button>
    </form>
</body>
</html>
