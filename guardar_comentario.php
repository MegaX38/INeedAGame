<?php
session_start();
require __DIR__ . '/config/db.php';

if (!isset($_SESSION['usuario_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: acceso.php?estado=requiere_login');
    exit;
}

$juegoId = (int)($_POST['juego_id'] ?? 0);
$puntuacion = (int)($_POST['puntuacion'] ?? 5);
$contenido = trim($_POST['contenido'] ?? '');

if ($juegoId <= 0 || mb_strlen($contenido) < 3 || mb_strlen($contenido) > 1500) {
    header("Location: juego.php?id={$juegoId}&estado=comentario_invalido#comentarios");
    exit;
}
if ($puntuacion < 1 || $puntuacion > 5) {
    $puntuacion = 5;
}

$stmt = $pdo->prepare('INSERT INTO comentarios (juego_id, usuario_id, contenido, puntuacion) VALUES (?, ?, ?, ?)');
$stmt->execute([$juegoId, (int)$_SESSION['usuario_id'], $contenido, $puntuacion]);

header("Location: juego.php?id={$juegoId}&estado=comentario_ok#comentarios");
exit;
