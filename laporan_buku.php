<?php
require 'koneksi.php';
require 'fpdf/fpdf.php';

// Query database
$sql = "SELECT * FROM buku ORDER BY id ASC";
$result = $conn->query($sql);
$buku = [];
$newId = 1;

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $row['id'] = $newId;
        $buku[] = $row;
        $newId++;
    }
}

class PDF extends FPDF {
    function NbLines($w, $txt) {
        if($w==0) return 1;
        $wmax = $w - 2*$this->cMargin;
        $s = str_replace("\r",'',$txt);
        $nb = strlen($s);
        if($nb > 0 && $s[$nb-1] == "\n") $nb--;
        $sep = -1;
        $i = 0;
        $j = 0;
        $l = 0;
        $nl = 1;
        while($i < $nb) {
            $c = $s[$i];
            if($c == "\n") {
                $i++;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
                continue;
            }
            if($c == ' ') $sep = $i;
            $l += $this->GetStringWidth($c);
            if($l > $wmax) {
                if($sep == -1) {
                    if($i == $j) $i++;
                }
                else $i = $sep + 1;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
            }
            else $i++;
        }
        return $nl;
    }

    function Header() {
        // Font untuk header
        $this->SetFont('Arial', 'B', 20);
        $this->Cell(0, 20, 'LAPORAN DAFTAR BUKU', 0, 1, 'C');
        $this->SetFont('Arial', 'I', 12);
        $this->Cell(0, 10, 'Generated on: ' . date('d/m/Y H:i:s'), 0, 1, 'C');
        $this->Ln(10);

        // Header Tabel
        $this->SetFont('Arial', 'B', 12);
        $this->SetFillColor(200, 220, 255);
        $this->Cell(15, 12, 'ID', 1, 0, 'C', true);
        $this->Cell(100, 12, 'Foto dan Judul Buku', 1, 0, 'C', true);
        $this->Cell(40, 12, 'Pengarang', 1, 0, 'C', true);
        $this->Cell(35, 12, 'Tahun Terbit', 1, 1, 'C', true);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Page ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }
}

// Buat instance PDF
$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage('P', 'A4');
$pdf->SetAutoPageBreak(true, 15);

// Set font untuk isi
$pdf->SetFont('Arial', '', 11);

// Isi tabel
foreach ($buku as $data) {
    $rowHeight = 40; // Tinggi baris default untuk gambar
    
    // Mulai posisi Y untuk baris ini
    $startY = $pdf->GetY();
    
    // Print ID
    $pdf->Cell(15, $rowHeight, $data['id'], 'LR', 0, 'C');
    
    // Posisi untuk foto dan judul
    $currentX = $pdf->GetX();
    $currentY = $pdf->GetY();
    
    // Tambahkan foto jika ada
    if (!empty($data['foto']) && file_exists($data['foto'])) {
        $pdf->Image($data['foto'], $currentX + 5, $currentY + 2, 30, 35);
        $pdf->SetXY($currentX + 40, $currentY); // Geser ke kanan untuk judul
        $pdf->MultiCell(60, 5, $data['judul'], 0, 'L');
    } else {
        $pdf->SetXY($currentX, $currentY);
        $pdf->MultiCell(100, 5, $data['judul'], 0, 'L');
    }
    
    // Reset posisi untuk sel berikutnya
    $pdf->SetXY($currentX + 100, $currentY);
    
    // Print pengarang dan tahun terbit
    $pdf->Cell(40, $rowHeight, $data['pengarang'], 'LR', 0, 'L');
    $pdf->Cell(35, $rowHeight, $data['tahun_terbit'], 'LR', 1, 'C');
    
    // Gambar garis horizontal bawah
    $pdf->Line($pdf->GetX(), $pdf->GetY(), $pdf->GetX() + 190, $pdf->GetY());
}

// Output PDF
$filename = "Amyatul_Janah_" . date("0073183547") . ".pdf";
$pdf->Output('D', $filename);
exit();
?>