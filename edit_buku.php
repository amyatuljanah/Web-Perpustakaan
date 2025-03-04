<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: index.php');
    exit();
}

require 'koneksi.php';

if (!isset($_GET['id'])) {
    header('Location: dashboard.php');
    exit();
}

$id = $_GET['id'];

// Mencegah SQL Injection dengan prepared statement
$stmt = $conn->prepare("SELECT * FROM buku WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$buku = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$buku) {
    header('Location: dashboard.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $judul = $_POST['judul'];
    $pengarang = $_POST['pengarang'];
    $tahun_terbit = $_POST['tahun_terbit'];

    // Cek apakah ada file foto yang di-upload
    if ($_FILES['foto']['error'] == 0) {
        // Validasi ekstensi file
        $allowedExtensions = ['jpg', 'jpeg', 'png'];
        $fileExtension = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));

        if (!in_array($fileExtension, $allowedExtensions)) {
            $error = "Hanya file gambar yang diperbolehkan (JPG, JPEG, PNG).";
        } else {
            // Hapus foto lama jika ada
            if ($buku['foto'] && file_exists($buku['foto'])) {
                unlink($buku['foto']); // Menghapus file foto lama
            }

            // Upload foto baru
            $foto = $_FILES['foto'];
            $fotoNama = uniqid('', true) . '.' . $fileExtension;
            $fotoPath = 'uploads/' . $fotoNama;

            if (move_uploaded_file($foto['tmp_name'], $fotoPath)) {
                echo "File berhasil diupload!";
            } else {
                $error = "Gagal mengupload file.";
            }
        }
    } else {
        // Jika tidak ada foto baru, pakai foto lama
        $fotoPath = $buku['foto'];
    }

    // Menggunakan prepared statement untuk update
    if (!isset($error)) {
        $stmt = $conn->prepare("UPDATE buku SET judul = ?, pengarang = ?, tahun_terbit = ?, foto = ? WHERE id = ?");
        $stmt->bind_param("ssisi", $judul, $pengarang, $tahun_terbit, $fotoPath, $id);

        if ($stmt->execute()) {
            // Menyimpan pesan sukses di session
            $_SESSION['message'] = 'Buku telah diperbarui';
    
            // Redirect ke dashboard setelah berhasil
            header('Location: dashboard.php');
            exit();
        } else {
            $error = "Gagal mengupdate buku: " . $conn->error;
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Buku</title>
    <link rel="stylesheet" href="edit_buku.css">
</head>
<body>
    <h1>Edit Buku</h1>
    <?php if (isset($error)): ?>
        <p style="color:red;"><?= htmlspecialchars($error); ?></p>
    <?php endif; ?>
    <form method="POST" action="" enctype="multipart/form-data">
        <label>Judul Buku:</label><br>
        <input type="text" name="judul" value="<?= htmlspecialchars($buku['judul']); ?>" required><br>
        <label>Pengarang:</label><br>
        <input type="text" name="pengarang" value="<?= htmlspecialchars($buku['pengarang']); ?>" required><br>
        <label>Tahun Terbit:</label><br>
        <input type="number" name="tahun_terbit" value="<?= htmlspecialchars($buku['tahun_terbit']); ?>" required><br>
        
        <!-- Menampilkan foto yang ada -->
        <?php if ($buku['foto']): ?>
            <label>Foto Buku Saat Ini:</label><br>
            <img src="<?= htmlspecialchars($buku['foto']); ?>" alt="Foto Buku" width="100"><br>
        <?php endif; ?>
        
        <label for="foto">Foto Buku Baru (Optional):</label>
        <input type="file" name="foto" accept="image/*"><br>
        
        <button type="submit">Update</button>
    </form>
</body>
</html>
