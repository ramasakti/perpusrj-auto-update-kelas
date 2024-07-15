<?php
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

// Koneksi ke database
$host = 'localhost';
$db = 'linspro';
$user = 'root';
$pass = 'root';

$dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

// Proses unggahan file
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file'];

    // Periksa apakah tidak ada kesalahan saat mengunggah
    if ($file['error'] == 0) {
        $filePath = 'uploads/' . basename($file['name']);
        move_uploaded_file($file['tmp_name'], $filePath);

        // Membaca file Excel
        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        $data = $sheet->toArray();

        // Mulai dari baris kedua untuk melewati header
        foreach ($data as $index => $row) {
            if ($index == 0) {
                continue; // Lewati baris header
            }

            $nama = $row[0];
            $organisasi = $row[1];
            $kojur = $row[1];
            $no_anggota = $row[2];
            $tempat_lahir = $row[3];
            $tanggal_lahir = $row[4];

            // Update query
            $sql = "UPDATE anggota SET Organisasi = :organisasi, Kojur = :kojur WHERE `No Anggota` = :no_anggota";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['organisasi' => $organisasi, 'kojur' => $kojur, 'no_anggota' => $no_anggota]);
        }

        echo "Update selesai!";
    } else {
        echo "Terjadi kesalahan saat mengunggah file.";
    }
} else {
    echo "Tidak ada file yang diunggah.";
}
?>
