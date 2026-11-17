<?php
session_start();
require __DIR__ . '/config/db.php';

$redirect = $_POST['redirect'] ?? 'index.php';
// Evitar open redirect
if (strpos($redirect, '://') !== false) 

if (!isset($_SESSION['usuario_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: acceso.php?estado=requiere_login');
    exit;
}

$tipo = $_POST['tipo_objetivo'] ?? '';
$objetivoId = (int)($_POST['objetivo_id'] ?? 0);
$valor = (int)($_POST['valor'] ?? 5);

if (!in_array($tipo, ['comentario', 'articulo'], true) || $objetivoId <= 0 || $valor < 1 || $valor > 5) {
    header('Location: ' . $redirect);
    exit;
}

$sep = (strpos($redirect, '?') !== false) ? '&' : '?';

try {
    $stmt = $pdo->prepare('INSERT INTO calificaciones (usuario_id, tipo_objetivo, objetivo_id, valor) VALUES (?, ?, ?, ?)');
    $stmt->execute([(int)$_SESSION['usuario_id'], $tipo, $objetivoId, $valor]);
    header('Location: ' . $redirect . $sep . 'estado=voto_ok');
    exit;
} catch (PDOException $e) {
    // Código 23000 = Violación de UNIQUE KEY (ya votó este elemento)
    if ($e->getCode() === '23000') {
        header('Location: ' . $redirect . $sep . 'estado=voto_duplicado');
        exit;
    }
    error_log('Error al calificar: ' . $e->getMessage());
    header('Location: ' . $redirect);
    exit;
}
