<?php
declare(strict_types=1);
require __DIR__ . '/config.php';
require __DIR__ . '/datos_paquetes.php';

$id = (int)($_GET['id'] ?? 0);

if (isset($PAQUETES[$id])) {
  $_SESSION['carrito'][$id] = ($_SESSION['carrito'][$id] ?? 0) + 1;
}

header('Location: index.php');
exit;
