<?php
session_start();
require __DIR__ . '/config/db.php';

if (!isset($_SESSION['usuario_id']) || !in_array($_SESSION['rol'] ?? '', ['moderador', 'admin'], true) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(403);
    exit('Acceso no autorizado');
}

$reporteId = (int)($_POST['reporte_id'] ?? 0);
$accion = $_POST['accion'] ?? '';
$modId = (int)$_SESSION['usuario_id'];

$stmt = $pdo->prepare('SELECT * FROM reportes WHERE id = ? LIMIT 1');
$stmt->execute([$reporteId]);
$rep = $stmt->fetch();

if ($rep) {
    if ($accion === 'aprobar') {
        $pdo->prepare("UPDATE reportes SET estado = 'resuelto', moderado_por = ? WHERE id = ?")->execute([$modId, $reporteId]);
    } elseif ($accion === 'rechazar') {
        $pdo->prepare("UPDATE reportes SET estado = 'rechazado', moderado_por = ? WHERE id = ?")->execute([$modId, $reporteId]);
    } elseif ($accion === 'ocultar_contenido') {
        if ($rep['tipo_objetivo'] === 'comentario' && $rep['objetivo_id']) {
            $pdo->prepare("UPDATE comentarios SET estado = 'oculto' WHERE id = ?")->execute([(int)$rep['objetivo_id']]);
        } elseif ($rep['tipo_objetivo'] === 'articulo' && $rep['objetivo_id']) {
            $pdo->prepare("UPDATE articulos SET estado = 'rechazado' WHERE id = ?")->execute([(int)$rep['objetivo_id']]);
        }
        $pdo->prepare("UPDATE reportes SET estado = 'resuelto', moderado_por = ? WHERE id = ?")->execute([$modId, $reporteId]);
    }
}

header('Location: moderacion.php?estado=actualizado');
exit;
