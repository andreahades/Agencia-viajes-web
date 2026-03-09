<?php
declare(strict_types=1);

// Detecta HTTPS (para cookie "secure")
$https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');

// Endurecer cookie de sesión (ANTES de session_start)
session_set_cookie_params([
  'lifetime' => 0,     // sesión hasta cerrar navegador (ajustable)
  'path' => '/',
  'secure' => $https,  // true si estás en HTTPS
  'httponly' => true,  // evita acceso desde JS
  'samesite' => 'Lax'
]);

session_start();

/* ---------- CSRF ---------- */
if (empty($_SESSION['csrf'])) {
  $_SESSION['csrf'] = bin2hex(random_bytes(16));
}

/* ---------- Control de inactividad ---------- */
$MAX_INACTIVIDAD = 15 * 60; // 15 min (ajusta a tu criterio)
$ahora = time();

if (isset($_SESSION['last_activity']) && ($ahora - (int)$_SESSION['last_activity']) > $MAX_INACTIVIDAD) {
  session_unset();
  session_destroy();
  header('Location: index.php?msg=sesion_expirada');
  exit;
}
$_SESSION['last_activity'] = $ahora;

/* ---------- Carrito ---------- */
if (!isset($_SESSION['carrito']) || !is_array($_SESSION['carrito'])) {
  $_SESSION['carrito'] = [];
}

/* ---------- Helpers ---------- */
function h(string $s): string {
  return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

function moneyCLP(int $v): string {
  return '$' . number_format($v, 0, ',', '.');
}

function require_csrf(): void {
  $csrf = $_POST['csrf'] ?? '';
  if (!hash_equals($_SESSION['csrf'] ?? '', $csrf)) {
    http_response_code(400);
    die('Solicitud inválida (CSRF).');
  }
}
