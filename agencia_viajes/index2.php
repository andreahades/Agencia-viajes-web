<?php
declare(strict_types=1);
require __DIR__ . '/config.php';
require __DIR__ . '/datos_paquetes.php';

$msg = $_GET['msg'] ?? '';

// Datos simulados de vuelos
$VUELOS = [
    ['origen' => 'Santiago', 'destino' => 'Lima', 'fecha' => '2026-03-15', 'precio' => 120000],
    ['origen' => 'Santiago', 'destino' => 'Buenos Aires', 'fecha' => '2026-03-20', 'precio' => 145000],
    ['origen' => 'Santiago', 'destino' => 'Rio de Janeiro', 'fecha' => '2026-03-25', 'precio' => 180000],
    ['origen' => 'Concepción', 'destino' => 'Lima', 'fecha' => '2026-03-15', 'precio' => 135000],
    ['origen' => 'Antofagasta', 'destino' => 'Buenos Aires', 'fecha' => '2026-03-20', 'precio' => 160000],
];

$resultadosVuelos = [];
$origenBusqueda = '';
$destinoBusqueda = '';
$fechaBusqueda = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['buscar_vuelo'])) {
    $origenBusqueda = trim($_POST['origen'] ?? '');
    $destinoBusqueda = trim($_POST['destino'] ?? '');
    $fechaBusqueda = trim($_POST['fecha'] ?? '');

    foreach ($VUELOS as $vuelo) {
        if (
            stripos($vuelo['origen'], $origenBusqueda) !== false &&
            stripos($vuelo['destino'], $destinoBusqueda) !== false &&
            $vuelo['fecha'] === $fechaBusqueda
        ) {
            $resultadosVuelos[] = $vuelo;
        }
    }
}
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

<section class="card">
<h2>✈️ Búsqueda de vuelos</h2>
<p class="hint">Busca vuelos por origen, destino y fecha de viaje.</p>

<form method="POST" class="form-grid">
    <div class="field">
        <label for="origen">Origen</label>
        <input
            type="text"
            name="origen"
            id="origen"
            value="<?= h($origenBusqueda) ?>"
            placeholder="Ej: Santiago"
            required
        >
    </div>

    <div class="field">
        <label for="destino">Destino</label>
        <input
            type="text"
            name="destino"
            id="destino"
            value="<?= h($destinoBusqueda) ?>"
            placeholder="Ej: Lima"
            required
        >
    </div>

    <div class="field">
        <label for="fecha">Fecha de viaje</label>
        <input
            type="date"
            name="fecha"
            id="fecha"
            value="<?= h($fechaBusqueda) ?>"
            required
        >
    </div>

    <div class="row">
        <button type="submit" name="buscar_vuelo" class="btn">Buscar vuelo</button>
    </div>
</form>

<?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['buscar_vuelo'])): ?>
<div class="flight-results">
    <h2>🔎 Resultados</h2>

    <?php if ($resultadosVuelos): ?>
        <?php foreach ($resultadosVuelos as $vuelo): ?>
            <div class="flight-item">
                <p><b>Origen:</b> <?= h($vuelo['origen']) ?></p>
                <p><b>Destino:</b> <?= h($vuelo['destino']) ?></p>
                <p><b>Fecha:</b> <?= h($vuelo['fecha']) ?></p>
                <p><b>Precio:</b> <?= h(moneyCLP($vuelo['precio'])) ?></p>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="msg">No se encontraron vuelos para los criterios ingresados.</div>
    <?php endif; ?>
</div>
<?php endif; ?>
</section>

</main>
</body>
</html>