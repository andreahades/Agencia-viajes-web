<?php
declare(strict_types=1);
require __DIR__ . "/db.php";

/* =========================
   1) VUELOS (resumen)
   ========================= */
$vueloStats = $pdo->query("
  SELECT
    COUNT(*) AS total_vuelos,
    COALESCE(SUM(plazas_disponibles), 0) AS total_plazas_disponibles,
    COALESCE(MIN(precio), 0) AS precio_min,
    COALESCE(AVG(precio), 0) AS precio_prom,
    COALESCE(MAX(precio), 0) AS precio_max
  FROM VUELO
")->fetch();

/* =========================
   2) HOTELES (resumen)
   ========================= */
$hotelStats = $pdo->query("
  SELECT
    COUNT(*) AS total_hoteles,
    COALESCE(SUM(habitaciones_disponibles), 0) AS total_habitaciones_disponibles,
    COALESCE(MIN(tarifa_noche), 0) AS tarifa_min,
    COALESCE(AVG(tarifa_noche), 0) AS tarifa_prom,
    COALESCE(MAX(tarifa_noche), 0) AS tarifa_max
  FROM HOTEL
")->fetch();

/* =========================
   3) RESERVAS (resumen)
   ========================= */
$reservaStats = $pdo->query("
  SELECT
    COUNT(*) AS total_reservas,
    COALESCE(SUM(CASE WHEN DATE(fecha_reserva) = CURDATE() THEN 1 ELSE 0 END), 0) AS reservas_hoy,
    COALESCE(SUM(CASE WHEN fecha_reserva >= (NOW() - INTERVAL 7 DAY) THEN 1 ELSE 0 END), 0) AS reservas_7dias
  FROM RESERVA
")->fetch();

/* =========================
   4) TOP HOTELES (ranking)
   ========================= */
$topHoteles = $pdo->query("
  SELECT
    h.nombre,
    h.ubicacion,
    COUNT(r.id_reserva) AS total_reservas
  FROM HOTEL h
  LEFT JOIN RESERVA r ON r.id_hotel = h.id_hotel
  GROUP BY h.id_hotel, h.nombre, h.ubicacion
  ORDER BY total_reservas DESC
  LIMIT 3
")->fetchAll();

/* =========================
   5) Helpers de formato
   ========================= */
function money(float $v): string {
  return number_format($v, 2, ",", ".");
}
function intfmt($v): string {
  return number_format((int)$v, 0, ",", ".");
}

$totalVuelos   = (int)($vueloStats["total_vuelos"] ?? 0);
$totalPlazas   = (int)($vueloStats["total_plazas_disponibles"] ?? 0);
$precioMin     = (float)($vueloStats["precio_min"] ?? 0);
$precioProm    = (float)($vueloStats["precio_prom"] ?? 0);
$precioMax     = (float)($vueloStats["precio_max"] ?? 0);

$totalHoteles  = (int)($hotelStats["total_hoteles"] ?? 0);
$totalHab      = (int)($hotelStats["total_habitaciones_disponibles"] ?? 0);
$tarifaMin     = (float)($hotelStats["tarifa_min"] ?? 0);
$tarifaProm    = (float)($hotelStats["tarifa_prom"] ?? 0);
$tarifaMax     = (float)($hotelStats["tarifa_max"] ?? 0);

$totalReservas = (int)($reservaStats["total_reservas"] ?? 0);
$reservasHoy   = (int)($reservaStats["reservas_hoy"] ?? 0);
$reservas7     = (int)($reservaStats["reservas_7dias"] ?? 0);
?>

<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Agencia - Panel</title>
  <link rel="stylesheet" href="styles.css">
  <style>
    /* Mini grid dashboard sin romper tu styles.css */
    .grid { display:grid; grid-template-columns: repeat(3, 1fr); gap: 16px; }
    @media (max-width: 900px){ .grid { grid-template-columns: 1fr; } }
    .kpi { border:1px solid #e5e7eb; border-radius: 12px; padding: 14px; background:#fff; }
    .kpi .label { font-size: 0.95rem; color:#475569; }
    .kpi .value { font-size: 1.6rem; font-weight: 700; margin-top: 6px; }
    .kpi .hint  { font-size: 0.9rem; color:#64748b; margin-top: 4px; }
    .section-title { margin: 0 0 10px 0; }
    .split { display:flex; gap:16px; }
    .split > .card { flex:1; }
    @media (max-width: 900px){ .split { flex-direction: column; } }
  </style>
</head>
<body>
  <div class="container">

    <!-- CABECERA + NAV -->
    <div class="card">
      <h1>🧳 Agencia de Viajes - Panel</h1>
      <p class="small">
        Panel de administración: disponibilidad, tarifas y reservas.
      </p>

      <nav>
        <a class="btnlink btn" href="crear_vuelo.php">✈️ Vuelos</a>
        <a class="btnlink btn" href="crear_hotel.php">🏨 Hoteles</a>
        <a class="btnlink btn" href="crear_reserva.php">🧾 Reservas</a>
        <a class="btnlink btn2" href="consulta_hoteles_populares.php">📊 Consulta avanzada</a>
      </nav>
    </div>

    <!-- KPI CARDS -->
    <div class="card">
      <h2 class="section-title">📌 Indicadores Clave</h2>
      <div class="grid">
        <div class="kpi">
          <div class="label">✈️ Vuelos registrados</div>
          <div class="value"><?= intfmt($totalVuelos) ?></div>
          </div>

        <div class="kpi">
          <div class="label">✈️ Plazas disponibles</div>
          <div class="value"><?= intfmt($totalPlazas) ?></div>
          <div class="hint">Suma de plazas_disponibles</div>
        </div>

        <div class="kpi">
          <div class="label">🏨 Habitaciones disponibles</div>
          <div class="value"><?= intfmt($totalHab) ?></div>
          </div>

        <div class="kpi">
          <div class="label">🏨 Hoteles registrados</div>
          <div class="value"><?= intfmt($totalHoteles) ?></div>
          </div>

        <div class="kpi">
          <div class="label">🧾 Reservas totales</div>
          <div class="value"><?= intfmt($totalReservas) ?></div>
          </div>

        <div class="kpi">
          <div class="label">🧾 Reservas recientes</div>
          <div class="value"><?= intfmt($reservas7) ?></div>
          <div class="hint">Últimos 7 días | Hoy: <?= intfmt($reservasHoy) ?></div>
        </div>
      </div>
    </div>

    <!-- DETALLE (VUELOS + HOTELES) -->
    <div class="split">
      <div class="card">
        <h2 class="section-title">✈️ Resumen de Vuelos</h2>
        <table>
          <tr><th>Indicador</th><th>Valor</th></tr>
          <tr><td>Total vuelos</td><td><b><?= intfmt($totalVuelos) ?></b></td></tr>
          <tr><td>Plazas disponibles</td><td><b><?= intfmt($totalPlazas) ?></b></td></tr>
          <tr><td>Precio mínimo</td><td><b>$ <?= money($precioMin) ?></b></td></tr>
          <tr><td>Precio promedio</td><td><b>$ <?= money($precioProm) ?></b></td></tr>
          <tr><td>Precio máximo</td><td><b>$ <?= money($precioMax) ?></b></td></tr>
        </table>
      </div>

      <div class="card">
        <h2 class="section-title">🏨 Resumen de Hoteles</h2>
        <table>
          <tr><th>Indicador</th><th>Valor</th></tr>
          <tr><td>Total hoteles</td><td><b><?= intfmt($totalHoteles) ?></b></td></tr>
          <tr><td>Habitaciones disponibles</td><td><b><?= intfmt($totalHab) ?></b></td></tr>
          <tr><td>Tarifa mínima</td><td><b>$ <?= money($tarifaMin) ?></b></td></tr>
          <tr><td>Tarifa promedio</td><td><b>$ <?= money($tarifaProm) ?></b></td></tr>
          <tr><td>Tarifa máxima</td><td><b>$ <?= money($tarifaMax) ?></b></td></tr>
        </table>
      </div>
    </div>

    <!-- TOP HOTELES -->
    <div class="card">
      <h2 class="section-title">🔥 Top 3 hoteles más reservados</h2>
      <p class="small">
        Ranking basado en reservas registradas.
      </p>
      <table>
        <thead>
          <tr><th>#</th><th>Hotel</th><th>Ubicación</th><th>Total reservas</th></tr>
        </thead>
        <tbody>
          <?php if (!$topHoteles): ?>
            <tr><td colspan="4">No hay datos aún. Registra vuelos/hoteles y luego reservas.</td></tr>
          <?php else: ?>
            <?php $i=1; foreach ($topHoteles as $h): ?>
              <tr>
                <td><?= $i++ ?></td>
                <td><?= htmlspecialchars($h["nombre"]) ?></td>
                <td><?= htmlspecialchars($h["ubicacion"]) ?></td>
                <td><b><?= intfmt((int)$h["total_reservas"]) ?></b></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

  </div>
</body>
</html>
