<?php
declare(strict_types=1);
require __DIR__ . "/db.php";

$sql = "
  SELECT
    h.id_hotel,
    h.nombre,
    h.ubicacion,
    COUNT(r.id_reserva) AS total_reservas
  FROM HOTEL h
  INNER JOIN RESERVA r ON r.id_hotel = h.id_hotel
  GROUP BY h.id_hotel, h.nombre, h.ubicacion
  HAVING COUNT(r.id_reserva) > 2
  ORDER BY total_reservas DESC
";

$rows = $pdo->query($sql)->fetchAll();
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Hoteles con más de 2 reservas</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="container">

  <div class="card">
    <h1>📊 Hoteles con más de 2 reservas</h1>
    <p class="small"><a href="index.php">⬅️ Volver al panel</a></p>
    <p class="small">Consulta avanzada: Reserva de Hoteles, su ubicacion y cantidad de reservas realizadas.</p>

    <table>
      <thead>
        <tr><th>ID</th><th>Hotel</th><th>Ubicación</th><th>Total reservas</th></tr>
      </thead>
      <tbody>
        <?php if (!$rows): ?>
          <tr><td colspan="4">No hay hoteles con más de 2 reservas aún. Registra más reservas.</td></tr>
        <?php else: ?>
          <?php foreach ($rows as $r): ?>
            <tr>
              <td><?= (int)$r["id_hotel"] ?></td>
              <td><?= htmlspecialchars($r["nombre"]) ?></td>
              <td><?= htmlspecialchars($r["ubicacion"]) ?></td>
              <td><?= (int)$r["total_reservas"] ?></td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

</div>
</body>
</html>
