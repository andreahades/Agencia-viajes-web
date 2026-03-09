<?php
declare(strict_types=1);

$host = "localhost";
$dbname = "AGENCIA";
$user = "root";
$pass = ""; // XAMPP normalmente vacío
$charset = "utf8mb4";

$dsn = "mysql:host={$host};dbname={$dbname};charset={$charset}";

$options = [
  PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
  $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
  exit("Conexión fallida. Revisa host/usuario/clave/BD y que MySQL esté iniciado.");
}
