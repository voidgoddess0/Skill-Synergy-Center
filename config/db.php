<?php
$dsn = "mysql:host=localhost;dbname=ssc;charset=utf8mb4";
$user = "root";
$pass = "";
$options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (Exception $e) {
    die("DB Connection failed: " . $e->getMessage());
}
?>