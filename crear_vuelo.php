<?php
declare(strict_types=1);
require __DIR__ . "/db.php";

$msg = null;
$msgType = "ok";

/* =========================
   1) INSERTAR VUELO
   ========================= */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

  $origen  = trim($_POST["origen"] ?? "");
  $destino = trim($_POST["destino"] ?? "");
  $fecha   = trim($_POST["fecha"] ?? "");

  $plazas  = filter_var($_POST["plazas_disponibles"] ?? null, FILTER_VALIDATE_INT);
  $precio  = filter_var($_POST["precio"] ?? null, FILTER_VALIDATE_FLOAT);

  if ($origen === "" || $destino === "" || $fecha === "" || $plazas === false || $precio === false || $plazas < 0 || $precio < 0) {
    $msg = "Error: completa todos los campos y asegúrate de que plazas/precio sean valores válidos (>= 0).";
    $msgType = "err";
  } else {
    $stmt = $pdo->prepare(
      "INSERT INTO VUELO (origen, destino, fecha, plazas_disponibles, precio)
       VALUES (:o, :d, :f, :p, :pr)"
    );
    $stmt->execute([
      ":o"  => $origen,
      ":d"  => $destino,
      ":f"  => $fecha,
      ":p"  => $plazas,
      ":pr" => $precio
    ]);

    $msg = "Vuelo ingresado correctamente.";
    $msgType = "ok";
  }
}

/* =========================
   2) RESUMEN GENERAL (KPI)
   ========================= */
$resumenVuelos = $pdo->query("
  SELECT
    COUNT(*) AS total_vuelos,
    COALESCE(SUM(plazas_disponibles), 0) AS total_plazas,
    COALESCE(MIN(precio), 0) AS precio_min,
    COALESCE(AVG(precio), 0) AS precio_prom,
    COALESCE(MAX(precio), 0) AS precio_max
  FROM VUELO
")->fetch();

/* =========================
   3) VUELOS DISPONIBLES POR DESTINO
      (solo plazas_disponibles > 0)
   ========================= */
$vuelosPorDestino = $pdo->query("
  SELECT
    destino,
    COUNT(*) AS total_vuelos,
    COALESCE(SUM(plazas_disponibles), 0) AS total_plazas
  FROM VUELO
  WHERE plazas_disponibles > 0
  GROUP BY destino
  ORDER BY total_vuelos DESC, total_plazas DESC, destino ASC
")->fetchAll();

/* =========================
   4) LISTADO (SELECT simple)
   ========================= */
$vuelos = $pdo->query("SELECT * FROM VUELO ORDER BY id_vuelo DESC")->fetchAll();

function money(float $v): string {
  return number_format($v, 2, ",", ".");
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Gestión de Vuelos</title>
  <link rel="stylesheet" href="styles.css">
  <script>
    function validarVuelo(){
      const origen = document.getElementById("origen").value.trim();
      const destino = document.getElementById("destino").value.trim();
      const fecha = document.getElementById("fecha").value;
      const plazas = Number(document.getElementById("plazas_disponibles").value);
      const precio = Number(document.getElementById("precio").value);

      if(!origen || !destino || !fecha){
        alert("Completa origen, destino y fecha.");
        return false;
      }
      if(Number.isNaN(plazas) || plazas < 0){
        alert("Plazas debe ser un número >= 0.");
        return false;
      }
      if(Number.isNaN(precio) || precio < 0){
        alert("Precio debe ser un número >= 0.");
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
      <h1>✈️ Gestión de Vuelos</h1>
      <p class="small"><a href="index.php">⬅️ Volver al panel</a></p>

      <?php if ($msg): ?>
        <div class="msg <?= $msgType === "ok" ? "ok" : "err" ?>">
          <?= htmlspecialchars($msg) ?>
        </div>
      <?php endif; ?>
    </div>

    <!-- RESUMEN GENERAL -->
    <div class="card">
      <h2>📊 Resumen de Vuelos (General)</h2>

      <table>
        <tr><th>Concepto</th><th>Valor</th></tr>

        <tr>
          <td>Total vuelos registrados</td>
          <td><b><?= (int)($resumenVuelos["total_vuelos"] ?? 0) ?></b></td>
        </tr>

        <tr>
          <td>Plazas disponibles totales</td>
          <td><b><?= (int)($resumenVuelos["total_plazas"] ?? 0) ?></b></td>
        </tr>

        <tr>
          <td>Precio mínimo</td>
          <td><b>$ <?= money((float)($resumenVuelos["precio_min"] ?? 0)) ?></b></td>
        </tr>

        <tr>
          <td>Precio promedio</td>
          <td><b>$ <?= money((float)($resumenVuelos["precio_prom"] ?? 0)) ?></b></td>
        </tr>

        <tr>
          <td>Precio máximo</td>
          <td><b>$ <?= money((float)($resumenVuelos["precio_max"] ?? 0)) ?></b></td>
        </tr>
      </table>
    </div>

    <!-- VUELOS DISPONIBLES POR DESTINO -->
    <div class="card">
      <h2>🌎 Vuelos disponibles por destino</h2>
      <table>
        <thead>
          <tr>
            <th>Destino</th>
            <th>Vuelos disponibles</th>
            <th>Plazas disponibles</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!$vuelosPorDestino): ?>
            <tr><td colspan="3">No hay vuelos disponibles (con plazas &gt; 0).</td></tr>
          <?php else: ?>
            <?php foreach ($vuelosPorDestino as $d): ?>
              <tr>
                <td><?= htmlspecialchars($d["destino"]) ?></td>
                <td><b><?= (int)$d["total_vuelos"] ?></b></td>
                <td><b><?= (int)$d["total_plazas"] ?></b></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- FORMULARIO -->
    <div class="card">
      <h2>➕ Ingresar nuevo vuelo</h2>

      <form method="post" onsubmit="return validarVuelo();">
        <label for="origen">Origen</label>
        <input id="origen" name="origen" type="text" required>

        <label for="destino">Destino</label>
        <input id="destino" name="destino" type="text" required>

        <label for="fecha">Fecha</label>
        <input id="fecha" name="fecha" type="date" required>

        <label for="plazas_disponibles">Plazas disponibles</label>
        <input id="plazas_disponibles" name="plazas_disponibles" type="number" min="0" required>

        <label for="precio">Precio</label>
        <input id="precio" name="precio" type="number" min="0" step="0.01" required>

        <button class="btn" type="submit">Guardar Vuelo</button>
      </form>
    </div>

    <!-- LISTADO -->
    <div class="card">
      <h2>📋 Listado VUELO (SELECT simple)</h2>

      <table>
        <thead>
          <tr>
            <th>ID</th><th>Origen</th><th>Destino</th><th>Fecha</th><th>Plazas</th><th>Precio</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!$vuelos): ?>
            <tr><td colspan="6">Aún no hay vuelos registrados.</td></tr>
          <?php else: ?>
            <?php foreach ($vuelos as $v): ?>
              <tr>
                <td><?= (int)$v["id_vuelo"] ?></td>
                <td><?= htmlspecialchars($v["origen"]) ?></td>
                <td><?= htmlspecialchars($v["destino"]) ?></td>
                <td><?= htmlspecialchars($v["fecha"]) ?></td>
                <td><?= (int)$v["plazas_disponibles"] ?></td>
                <td>$ <?= money((float)$v["precio"]) ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

  </div>
</body>
</html>
