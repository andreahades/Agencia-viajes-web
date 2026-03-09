<?php
declare(strict_types=1);
require __DIR__ . "/db.php";

$msg = null;
$msgType = "ok";

/* =========================
   1) INSERTAR RESERVA
   ========================= */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

  $id_cliente = filter_var($_POST["id_cliente"] ?? null, FILTER_VALIDATE_INT);
  $id_vuelo   = filter_var($_POST["id_vuelo"] ?? null, FILTER_VALIDATE_INT);
  $id_hotel   = filter_var($_POST["id_hotel"] ?? null, FILTER_VALIDATE_INT);

  if ($id_cliente === false || $id_vuelo === false || $id_hotel === false) {
    $msg = "Datos inválidos.";
    $msgType = "err";
  } else {
    $stmt = $pdo->prepare("
      INSERT INTO RESERVA (id_cliente, id_vuelo, id_hotel)
      VALUES (:c, :v, :h)
    ");
    $stmt->execute([
      ":c"=>$id_cliente,
      ":v"=>$id_vuelo,
      ":h"=>$id_hotel
    ]);

    $msg = "Reserva registrada correctamente.";
  }
}

/* =========================
   2) SELECT PARA COMBO BOX
   ========================= */
$vuelos  = $pdo->query("SELECT id_vuelo, destino FROM VUELO")->fetchAll();
$hoteles = $pdo->query("SELECT id_hotel, nombre FROM HOTEL")->fetchAll();

/* =========================
   3) RESUMEN GENERAL
   ========================= */
$resumenReservas = $pdo->query("
  SELECT
    COUNT(*) total_reservas,
    SUM(CASE WHEN DATE(fecha_reserva)=CURDATE() THEN 1 ELSE 0 END) reservas_hoy,
    SUM(CASE WHEN fecha_reserva >= NOW()-INTERVAL 7 DAY THEN 1 ELSE 0 END) reservas_7dias
  FROM RESERVA
")->fetch();

/* =========================
   4) RESERVAS POR HOTEL
   ========================= */
$reservasHotel = $pdo->query("
  SELECT h.nombre, COUNT(r.id_reserva) total
  FROM HOTEL h
  LEFT JOIN RESERVA r ON r.id_hotel = h.id_hotel
  GROUP BY h.id_hotel
  ORDER BY total DESC
")->fetchAll();

/* =========================
   5) RESERVAS POR DESTINO
   ========================= */
$reservasDestino = $pdo->query("
  SELECT v.destino, COUNT(r.id_reserva) total
  FROM VUELO v
  LEFT JOIN RESERVA r ON r.id_vuelo = v.id_vuelo
  GROUP BY v.destino
  ORDER BY total DESC
")->fetchAll();

/* =========================
   6) LISTADO SIMPLE
   ========================= */
$reservas = $pdo->query("SELECT * FROM RESERVA ORDER BY id_reserva DESC")->fetchAll();

?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Gestión de Reservas</title>
<link rel="stylesheet" href="styles.css">
<script>
function validar(){
  if(!id_cliente.value || !id_vuelo.value || !id_hotel.value){
    alert("Completa todos los campos");
    return false;
  }
  return true;
}
</script>
</head>
<body>

<div class="container">

<div class="card">
<h1>🧾 Gestión de Reservas</h1>
<a href="index.php">⬅ Volver</a>

<?php if($msg): ?>
<div class="msg <?= $msgType==="ok"?"ok":"err" ?>">
<?= $msg ?>
</div>
<?php endif; ?>
</div>

<!-- RESUMEN -->
<div class="card">
<h2>📊 Resumen Reservas</h2>
<table>
<tr><th>Total</th><th>Hoy</th><th>Últimos 7 días</th></tr>
<tr>
<td><?= $resumenReservas["total_reservas"] ?? 0 ?></td>
<td><?= $resumenReservas["reservas_hoy"] ?? 0 ?></td>
<td><?= $resumenReservas["reservas_7dias"] ?? 0 ?></td>
</tr>
</table>
</div>

<!-- RESERVAS POR HOTEL -->
<div class="card">
<h2>🏨 Reservas por Hotel</h2>
<table>
<tr><th>Hotel</th><th>Reservas</th></tr>
<?php foreach($reservasHotel as $h): ?>
<tr>
<td><?= $h["nombre"] ?></td>
<td><?= $h["total"] ?></td>
</tr>
<?php endforeach; ?>
</table>
</div>

<!-- RESERVAS POR DESTINO -->
<div class="card">
<h2>✈ Reservas por Destino</h2>
<table>
<tr><th>Destino</th><th>Reservas</th></tr>
<?php foreach($reservasDestino as $d): ?>
<tr>
<td><?= $d["destino"] ?></td>
<td><?= $d["total"] ?></td>
</tr>
<?php endforeach; ?>
</table>
</div>

<!-- FORMULARIO -->
<div class="card">
<h2>➕ Nueva Reserva</h2>
<form method="post" onsubmit="return validar()">

<label>ID Cliente</label>
<input type="number" name="id_cliente" id="id_cliente" required>

<label>Vuelo</label>
<select name="id_vuelo" id="id_vuelo" required>
<option value="">Seleccione</option>
<?php foreach($vuelos as $v): ?>
<option value="<?= $v["id_vuelo"] ?>">
<?= $v["destino"] ?>
</option>
<?php endforeach; ?>
</select>

<label>Hotel</label>
<select name="id_hotel" id="id_hotel" required>
<option value="">Seleccione</option>
<?php foreach($hoteles as $h): ?>
<option value="<?= $h["id_hotel"] ?>">
<?= $h["nombre"] ?>
</option>
<?php endforeach; ?>
</select>

<button class="btn">Guardar Reserva</button>
</form>
</div>

<!-- LISTADO -->
<div class="card">
<h2>📋 Listado Reservas</h2>
<table>
<tr>
<th>ID</th><th>Cliente</th><th>Fecha</th><th>Vuelo</th><th>Hotel</th>
</tr>

<?php foreach($reservas as $r): ?>
<tr>
<td><?= $r["id_reserva"] ?></td>
<td><?= $r["id_cliente"] ?></td>
<td><?= $r["fecha_reserva"] ?></td>
<td><?= $r["id_vuelo"] ?></td>
<td><?= $r["id_hotel"] ?></td>
</tr>
<?php endforeach; ?>
</table>
</div>

</div>
</body>
</html>
