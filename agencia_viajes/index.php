<?php
declare(strict_types=1);
require __DIR__ . '/config.php';
require __DIR__ . '/datos_paquetes.php';
$msg = $_GET['msg'] ?? '';
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Agencia de Viajes | Catálogo</title>
<link rel="stylesheet" href="css/styles.css">
</head>
<body>
<header>
<h1>Agencia de Viajes | Paquetes Turísticos</h1>
</header>
<main>
<section class="card">
<h2>📦 Catálogo de paquetes</h2>
<p class="hint">Agrega paquetes al carrito.</p>
<?php if ($msg === 'sesion_expirada'): ?>
<div class="msg">Tu sesión expiró por inactividad. Por seguridad, se reinició el estado.</div>
<?php endif; ?>
<div class="list">
<?php foreach ($PAQUETES as $id => $p): ?>
<div class="item">
<div>
<b><?= h($p['nombre']) ?></b>
<small>Fecha: <?= h($p['fecha']) ?> • Precio: <?= h(moneyCLP($p['precio'])) ?></small>
</div>
<a class="btn" href="agregar.php?id=<?= (int)$id ?>">Agregar</a>
</div>
<?php endforeach; ?>
</div>
</section>
<aside class="card">
<h2>🛒 Acceso rápido</h2>
<p class="hint">Revisa tu selección, modifica cantidades o finaliza.</p>
<div class="row">
<a class="btn secondary" href="carrito.php">Ir al carrito</a>
<a class="btn danger" href="vaciar.php">Vaciar carrito</a>
</div>
<p class="hint" style="margin-top:12px;">
Ítems en carrito: <b><?= (int)array_sum($_SESSION['carrito']) ?></b>
</p>
</aside>
</main>
</body>
</html>