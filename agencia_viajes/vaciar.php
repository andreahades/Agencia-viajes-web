<?php
declare(strict_types=1);
require __DIR__ . '/config.php';

$_SESSION['carrito'] = [];
header('Location: carrito.php');
exit;
