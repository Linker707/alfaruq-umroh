<?php
// Konfigurasi database
$host = 'localhost'; // Ganti dengan host database Anda (misalnya, '127.0.0.1' atau server hosting)
$dbname = 'alfaruq_umroh'; // Nama database (sesuai schema di bawah)
$username = 'root'; // Ganti dengan username database Anda
$password = ''; // Ganti dengan password database Anda
try {
    // Koneksi PDO dengan error mode exception
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Tangani error koneksi (jangan tampilkan di production)
    die("Koneksi database gagal: " . $e->getMessage());
}
?>