<?php
declare(strict_types=1);
require __DIR__ . '/config.php';

$id = (int)($_GET['id'] ?? 0);
unset($_SESSION['carrito'][$id]);

header('Location: carrito.php');
exit;
