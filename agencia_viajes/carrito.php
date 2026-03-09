<?php
declare(strict_types=1);
require __DIR__ . '/config.php';
require __DIR__ . '/datos_paquetes.php';
$carrito = $_SESSION['carrito'];
$subtotal = 0;
foreach ($carrito as $id => $cantidad) {
if (isset($PAQUETES[$id])) {
$subtotal += $PAQUETES[$id]['precio'] * (int)$cantidad;
}
}
$msg = $_GET['msg'] ?? '';
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Agencia de Viajes | Carrito</title>
<link rel="stylesheet" href="css/styles.css">
</head>
<body>
<header>
<h1>Agencia de Viajes | Carrito de Paquetes</h1>
</header>
<main>
<section class="card">
<h2>🛒 Carrito</h2>
<p class="hint">Actualiza cantidades, elimina ítems o finaliza la compra.</p>
<?php if ($msg === 'finalizado'): ?>
<div class="msg">Compra finalizada. El carrito fue limpiado correctamente.</div>
<?php endif; ?>
<?php if (empty($carrito)): ?>
<p class="hint">El carrito está vacío.</p>
<div class="row">
<a class="btn secondary" href="index.php">Volver al catálogo</a>
</div>
<?php else: ?>
<form method="post" action="actualizar.php">
<input type="hidden" name="csrf" value="<?= h($_SESSION['csrf']) ?>">
<table>
<thead>
<tr>
<th>Paquete</th>
<th>Cant.</th>
<th>Precio</th>
<th></th>
</tr>
</thead>
<tbody>
<?php foreach ($carrito as $id => $cantidad): ?>
<?php if (!isset($PAQUETES[$id])) continue; ?>
<tr>
<td><?= h($PAQUETES[$id]['nombre']) ?><br><small><?= h($PAQUETES[$id]['fecha']) ?></small></td>
<td>
<input class="qty" type="number" min="0" max="10"
name="cantidades[<?= (int)$id ?>]"
value="<?= (int)$cantidad ?>">
</td>
<td><?= h(moneyCLP($PAQUETES[$id]['precio'] * (int)$cantidad)) ?></td>
<td><a class="btn danger" href="eliminar.php?id=<?= (int)$id ?>">Eliminar</a></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<div class="total">Total: <?= h(moneyCLP($subtotal)) ?></div>
<div class="row">
<button class="btn" type="submit">Actualizar</button>
<a class="btn danger" href="vaciar.php">Vaciar</a>
<a class="btn secondary" href="index.php">Seguir comprando</a>
</div>
</form>
<form method="post" action="finalizar.php" style="margin-top:10px;">
<input type="hidden" name="csrf" value="<?= h($_SESSION['csrf']) ?>">
<button class="btn secondary" type="submit">Finalizar compra</button>
</form>
<?php endif; ?>
</section>
<aside class="card">
<h2> Aprovecha Ofertas!✈️</h2>
<p class="hint">
No te pierdas de Ofertas exclusivas! Consulta a tu agente de viajes preferido por el programa de viajero frecuente! Suma descuentos por varios viajes con estadia y seguro incluido!.
</p>
</aside>
</main>
</body>
</html>