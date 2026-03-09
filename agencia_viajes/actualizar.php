<?php
declare(strict_types=1);
require __DIR__ . '/config.php';
require __DIR__ . '/datos_paquetes.php';

require_csrf();

$cantidades = $_POST['cantidades'] ?? [];
if (is_array($cantidades)) {
  foreach ($cantidades as $idStr => $qtyStr) {
    $id = (int)$idStr;
    $qty = (int)$qtyStr;

    if (!isset($_SESSION['carrito'][$id])) continue;

    if ($qty <= 0) {
      unset($_SESSION['carrito'][$id]);
    } else {
      $_SESSION['carrito'][$id] = min($qty, 10);
    }
  }
}

header('Location: carrito.php');
exit;
