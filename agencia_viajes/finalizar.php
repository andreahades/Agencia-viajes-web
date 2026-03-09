<?php
declare(strict_types=1);
require __DIR__ . '/config.php';

require_csrf();

// (Simulación) Aquí iría: procesar pago + guardar orden
// Luego, limpiar carrito para cerrar el flujo
$_SESSION['carrito'] = [];

// Medida extra: regenerar ID al finalizar flujo crítico
session_regenerate_id(true);

header('Location: carrito.php?msg=finalizado');
exit;
