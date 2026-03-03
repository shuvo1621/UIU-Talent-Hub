<?php
/*
 * db connection settings
 */

$host = 'localhost';
$db = 'uiu_talenthub';
$user = 'root'; // default for xampp
$pass = '';     // empty for xampp
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    // connected
} catch (\PDOException $e) {
    // show error for local debugging
    die("Database connection failed: " . $e->getMessage());
}
?>