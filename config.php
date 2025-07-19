<?php
// config.php
// ——————————————————————————————————————————————————————————
// Koneksi PDO ke MySQL
$host    = 'localhost';
$db      = 'ticketing';
$user    = 'root';
$pass    = '';           // Ganti jika pakai password MySQL
$charset = 'utf8mb4';

$dsn     = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    echo "Database Connection Failed: " . $e->getMessage();
    exit;
}
?>
