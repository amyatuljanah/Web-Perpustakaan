<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: index.php');
    exit();
}

// Jika ada pesan sukses (dari session), tampilkan notifikasi
if (isset($_SESSION['message'])) {
    ?>
    <div class="notification-overlay" id="notification">
        <div class="notification-box">
            <div class="notification-message">
                <?php echo $_SESSION['message']; ?>
            </div>
        </div>
    </div>

    <script>
    // Fungsi untuk menutup notifikasi
    function closeNotification() {
        document.getElementById('notification').style.display = 'none';
    }

    // Auto hide after 3 seconds
    setTimeout(closeNotification, 3000);
    </script>
    <?php
    unset($_SESSION['message']); // Menghapus pesan setelah ditampilkan
}






require 'koneksi.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Inisialisasi variabel $search_query
$search_query = "";

// Cek apakah ada pencarian
if (isset($_GET['search'])) { // Ganti $_POST dengan $_GET agar URL bisa menerima parameter search
    $search_query = $_GET['search'];
    // Mencegah SQL Injection dengan prepared statement
    $stmt = $conn->prepare("SELECT * FROM buku WHERE judul LIKE ? OR pengarang LIKE ?");
    $search_param = "%" . $search_query . "%";
    $stmt->bind_param("ss", $search_param, $search_param);
    $stmt->execute();
    $buku = $stmt->get_result();
} else {
    // Jika tidak ada pencarian, tampilkan semua data
    $buku = $conn->query("SELECT * FROM buku ORDER BY id ASC");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard Admin</title>
    <link rel="stylesheet" href="dashboard.css">
    <script>
        // Fungsi pencarian buku tanpa form
        function searchBooks() {
            const searchQuery = document.getElementById('search-input').value; // Ambil nilai input
            window.location.href = "?search=" + encodeURIComponent(searchQuery); // Redirect ke URL dengan query
        }

        // Fungsi untuk menangani pencarian dengan tombol Enter
        document.addEventListener("DOMContentLoaded", () => {
            const input = document.getElementById('search-input');
            input.addEventListener("keydown", function (e) {
                if (e.key === "Enter") {
                    e.preventDefault(); // Hindari submit form default
                    searchBooks(); // Panggil fungsi searchBooks()
                }
            });
        });
    </script>
</head>
<body>
    <h1>Dashboard Admin</h1>
     <!-- Input Pencarian -->
    <div class="search-container">
      <input type="text" id="search-input" value="<?= htmlspecialchars($search_query); ?>" placeholder="Cari buku" autocomplete="off">
       <button onclick="searchBooks()">Cari</button>
    </div>

    <h2 style="text-align: left; width: 100%; margin-top: 20px; padding-left: 509px;">Daftar Buku</h2>
    <br>

 
    <!-- Tombol Tambah Buku -->
    <div style="display: flex; align-items: center; margin-bottom: 20px; gap: 20px;">
    <div style="display: flex; gap: 10px;">
        <a href="tambah_buku.php" style="text-decoration: none; padding: 10px 20px; background-color: #4CAF50; color: white; border: none; border-radius: 5px;">
            Tambah Buku
        </a>

        <a href="laporan_buku.php" style="text-decoration: none; padding: 10px 20px; background-color: #2196F3; color: white; border: none; border-radius: 5px;">
            Download Laporan Buku
        </a>
    </div>
</div>
  
<a href="logout.php" class="logout-btn" onclick="return confirm('Yakin ingin keluar?');">Logout</a> 


<table border="1" cellpadding="10" cellspacing="0" class="table-container">

    <tr>
        <th>ID</th>
        <th>Foto dan Judul</th>
        <th>Pengarang</th>
        <th>Tahun Terbit</th>
        <th>Aksi</th>
    </tr>
    <?php
    if ($buku->num_rows > 0) {
        $id = 1;
        while ($row = $buku->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $id++ . "</td>"; // Nomor
            
            // Kolom Foto dan Judul
            echo "<td>";
            // Tampilkan foto jika ada
            if ($row['foto']) {
                echo "<img src='" . htmlspecialchars($row['foto']) . "' width='100' style='margin: 0 auto;'>"; // Menampilkan foto
            } else {
                echo "<img src='default.jpg' width='100' style='margin: 0 auto;'>"; // Gambar default jika tidak ada foto
            }
            // Menampilkan judul di bawah gambar
            echo "<p style='text-align: center;'>" . htmlspecialchars($row['judul']) . "</p>";
            echo "</td>";

            echo "<td>" . htmlspecialchars($row['pengarang']) . "</td>"; // Pengarang
            echo "<td>" . htmlspecialchars($row['tahun_terbit']) . "</td>"; // Tahun Terbit
            echo "<td>
                    <a href='edit_buku.php?id=" . htmlspecialchars($row['id']) . "'>Edit</a> |
                    <a href='hapus_buku.php?id=" . htmlspecialchars($row['id']) . "' onclick=\"return confirm('Yakin ingin menghapus buku ini?')\">Hapus</a>
                  </td>";
            echo "</tr>";
        }
    }
    ?>
    </table>

  


</body>
</html>

<?php
mysqli_query($conn, "ALTER TABLE buku AUTO_INCREMENT = 1");
// Tutup koneksi
mysqli_close($conn);
?>
