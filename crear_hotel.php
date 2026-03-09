<?php
declare(strict_types=1);
require __DIR__ . "/db.php";

$msg = null;
$msgType = "ok";

/* =========================
   1) INSERTAR HOTEL
   ========================= */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

  $nombre = trim($_POST["nombre"] ?? "");
  $ubicacion = trim($_POST["ubicacion"] ?? "");

  $habitaciones = filter_var($_POST["habitaciones_disponibles"] ?? null, FILTER_VALIDATE_INT);
  $tarifa = filter_var($_POST["tarifa_noche"] ?? null, FILTER_VALIDATE_FLOAT);

  if ($nombre === "" || $ubicacion === "" || $habitaciones === false || $tarifa === false || $habitaciones < 0 || $tarifa < 0) {
    $msg = "Error: completa todos los campos y asegúrate de que habitaciones/tarifa sean valores válidos (>= 0).";
    $msgType = "err";
  } else {
    $stmt = $pdo->prepare(
      "INSERT INTO HOTEL (nombre, ubicacion, habitaciones_disponibles, tarifa_noche)
       VALUES (:n, :u, :h, :t)"
    );
    $stmt->execute([
      ":n" => $nombre,
      ":u" => $ubicacion,
      ":h" => $habitaciones,
      ":t" => $tarifa
    ]);

    $msg = "Hotel ingresado correctamente.";
    $msgType = "ok";
  }
}

/* =========================
   2) RESUMEN GENERAL (KPI)
   ========================= */
$resumenHoteles = $pdo->query("
  SELECT
    COUNT(*) AS total_hoteles,
    COALESCE(SUM(habitaciones_disponibles), 0) AS total_habitaciones,
    COALESCE(MIN(tarifa_noche), 0) AS tarifa_min,
    COALESCE(AVG(tarifa_noche), 0) AS tarifa_prom,
    COALESCE(MAX(tarifa_noche), 0) AS tarifa_max
  FROM HOTEL
")->fetch();

/* =========================
   3) DISPONIBILIDAD POR UBICACIÓN
      (solo habitaciones_disponibles > 0)
   ========================= */
$habitacionesPorUbicacion = $pdo->query("
  SELECT
    ubicacion,
    COUNT(*) AS total_hoteles,
    COALESCE(SUM(habitaciones_disponibles), 0) AS total_habitaciones
  FROM HOTEL
  WHERE habitaciones_disponibles > 0
  GROUP BY ubicacion
  ORDER BY total_habitaciones DESC, total_hoteles DESC, ubicacion ASC
")->fetchAll();

/* =========================
   4) LISTADO (SELECT simple)
   ========================= */
$hoteles = $pdo->query("SELECT * FROM HOTEL ORDER BY id_hotel DESC")->fetchAll();

function money(float $v): string {
  return number_format($v, 2, ",", ".");
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Gestión de Hoteles</title>
  <link rel="stylesheet" href="styles.css">
  <script>
    function validarHotel(){
      const nombre = document.getElementById("nombre").value.trim();
      const ubicacion = document.getElementById("ubicacion").value.trim();
      const hab = Number(document.getElementById("habitaciones_disponibles").value);
      const tarifa = Number(document.getElementById("tarifa_noche").value);

      if(!nombre || !ubicacion){
        alert("Completa nombre y ubicación.");
        return false;
      }
      if(Number.isNaN(hab) || hab < 0){
        alert("Habitaciones debe ser un número >= 0.");
        return false;
      }
      if(Number.isNaN(tarifa) || tarifa < 0){
        alert("Tarifa debe ser un número >= 0.");
        return false;
      }
      return true;
    }
  </script>
</head>
<body>
  <div class="container">

    <!-- CABECERA -->
    <div class="card">
      <h1>🏨 Gestión de Hoteles</h1>
      <p class="small"><a href="index.php">⬅️ Volver al panel</a></p>

      <?php if ($msg): ?>
        <div class="msg <?= $msgType === "ok" ? "ok" : "err" ?>">
          <?= htmlspecialchars($msg) ?>
        </div>
      <?php endif; ?>
    </div>

    <!-- RESUMEN GENERAL -->
    <div class="card">
      <h2>📊 Resumen de Hoteles (General)</h2>

      <table>
        <tr><th>Concepto</th><th>Valor</th></tr>

        <tr>
          <td>Total hoteles registrados</td>
          <td><b><?= (int)($resumenHoteles["total_hoteles"] ?? 0) ?></b></td>
        </tr>

        <tr>
          <td>Habitaciones disponibles totales</td>
          <td><b><?= (int)($resumenHoteles["total_habitaciones"] ?? 0) ?></b></td>
        </tr>

        <tr>
          <td>Tarifa mínima</td>
          <td><b>$ <?= money((float)($resumenHoteles["tarifa_min"] ?? 0)) ?></b></td>
        </tr>

        <tr>
          <td>Tarifa promedio</td>
          <td><b>$ <?= money((float)($resumenHoteles["tarifa_prom"] ?? 0)) ?></b></td>
        </tr>

        <tr>
          <td>Tarifa máxima</td>
          <td><b>$ <?= money((float)($resumenHoteles["tarifa_max"] ?? 0)) ?></b></td>
        </tr>
      </table>
    </div>

    <!-- DISPONIBILIDAD POR UBICACIÓN -->
    <div class="card">
      <h2>📍 Habitaciones disponibles por ubicación</h2>
      <p class="small">
        Se consideran únicamente hoteles con <b>habitaciones_disponibles &gt; 0</b>. Se muestra también cuántos hoteles aportan disponibilidad.
      </p>

      <table>
        <thead>
          <tr>
            <th>Ubicación</th>
            <th>Hoteles disponibles</th>
            <th>Habitaciones disponibles</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!$habitacionesPorUbicacion): ?>
            <tr><td colspan="3">No hay hoteles con habitaciones disponibles (habitaciones &gt; 0).</td></tr>
          <?php else: ?>
            <?php foreach ($habitacionesPorUbicacion as $u): ?>
              <tr>
                <td><?= htmlspecialchars($u["ubicacion"]) ?></td>
                <td><b><?= (int)$u["total_hoteles"] ?></b></td>
                <td><b><?= (int)$u["total_habitaciones"] ?></b></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- FORMULARIO -->
    <div class="card">
      <h2>➕ Ingresar nuevo hotel</h2>

      <form method="post" onsubmit="return validarHotel();">
        <label for="nombre">Nombre</label>
        <input id="nombre" name="nombre" type="text" required>

        <label for="ubicacion">Ubicación</label>
        <input id="ubicacion" name="ubicacion" type="text" required>

        <label for="habitaciones_disponibles">Habitaciones disponibles</label>
        <input id="habitaciones_disponibles" name="habitaciones_disponibles" type="number" min="0" required>

        <label for="tarifa_noche">Tarifa por noche</label>
        <input id="tarifa_noche" name="tarifa_noche" type="number" min="0" step="0.01" required>

        <button class="btn" type="submit">Guardar Hotel</button>
      </form>
    </div>

    <!-- LISTADO -->
    <div class="card">
      <h2>📋 Listado HOTEL (SELECT simple)</h2>

      <table>
        <thead>
          <tr>
            <th>ID</th><th>Nombre</th><th>Ubicación</th><th>Habitaciones</th><th>Tarifa</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!$hoteles): ?>
            <tr><td colspan="5">Aún no hay hoteles registrados.</td></tr>
          <?php else: ?>
            <?php foreach ($hoteles as $h): ?>
              <tr>
                <td><?= (int)$h["id_hotel"] ?></td>
                <td><?= htmlspecialchars($h["nombre"]) ?></td>
                <td><?= htmlspecialchars($h["ubicacion"]) ?></td>
                <td><?= (int)$h["habitaciones_disponibles"] ?></td>
                <td>$ <?= money((float)$h["tarifa_noche"]) ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

  </div>
</body>
</html>
