<?php
session_start();
require __DIR__ . '/config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: reportes.php'); exit;
}
$tipos = ['Reporte de contenido', 'Sugerencia', 'Problema técnico', 'Consulta general'];
$tiposObj = ['general', 'comentario', 'articulo', 'juego'];

$tipo = trim($_POST['tipo'] ?? '');
$tipoObj = in_array($_POST['tipo_objetivo'] ?? '', $tiposObj, true) ? $_POST['tipo_objetivo'] : 'general';
$objetivoId = !empty($_POST['objetivo_id']) ? (int)$_POST['objetivo_id'] : null;
$correo = trim($_POST['correo'] ?? '');
$contenido = trim($_POST['contenido'] ?? '');
$mensaje = trim($_POST['mensaje'] ?? '');

if (!in_array($tipo, $tipos, true) || $mensaje === '' || mb_strlen($mensaje) > 5000) {
    header('Location: reportes.php?estado=datos_invalidos#solicitud'); exit;
}
if ($correo !== '' && (!filter_var($correo, FILTER_VALIDATE_EMAIL) || strlen($correo) > 190)) {
    header('Location: reportes.php?estado=correo_invalido#solicitud'); exit;
}
if (mb_strlen($contenido) > 180) {
    header('Location: reportes.php?estado=contenido_largo#solicitud'); exit;
}

$usuarioId = isset($_SESSION['usuario_id']) ? (int)$_SESSION['usuario_id'] : null;
try {
    $stmt = $pdo->prepare('INSERT INTO reportes (usuario_id, tipo, tipo_objetivo, objetivo_id, correo_contacto, contenido_relacionado, descripcion) VALUES (?, ?, ?, ?, ?, ?, ?)');
    $stmt->execute([$usuarioId, $tipo, $tipoObj, $objetivoId, $correo !== '' ? $correo : null, $contenido !== '' ? $contenido : null, $mensaje]);
    header('Location: reportes.php?estado=enviado#solicitud'); exit;
} catch (PDOException $e) {
    error_log('Error al guardar reporte: ' . $e->getMessage());
    header('Location: reportes.php?estado=error#solicitud'); exit;
}
