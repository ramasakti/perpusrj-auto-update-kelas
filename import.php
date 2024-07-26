<?php
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
        if (move_uploaded_file($file['tmp_name'], $filePath)) {

            // Membaca file CSV
            if (($handle = fopen($filePath, "r")) !== FALSE) {
                // Lewati baris pertama (header)
                fgetcsv($handle, 1000, ",");

                // Baca setiap baris data
                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    $no_anggota = $data[2];
                    $organisasi = $data[1];
                    $nama = $data[0];
                    $kojur = $data[1];

                    // Update query
                    $sql = "INSERT INTO anggota (`No Anggota`, `Organisasi`, `Nama Lengkap`, `Kojur`) VALUES (:no_anggota, :organisasi, :nama, :kojur)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([
                        'no_anggota' => $no_anggota,
                        'organisasi' => $organisasi,
                        'nama' => $nama,
                        'kojur' => $kojur
                    ]);
                }
                fclose($handle);
                echo "Update selesai!";
            } else {
                echo "Tidak dapat membuka file.";
            }
        } else {
            echo "Terjadi kesalahan saat memindahkan file.";
        }
    } else {
        echo "Terjadi kesalahan saat mengunggah file.";
    }
} else {
    echo "Tidak ada file yang diunggah.";
}
?>
