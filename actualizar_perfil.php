<?php
session_start();
require __DIR__ . '/config/db.php';

if (!isset($_SESSION['usuario_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: acceso.php?estado=requiere_login');
    exit;
}

$uid = (int)$_SESSION['usuario_id'];
$nombre = trim($_POST['nombre_usuario'] ?? '');
$correo = strtolower(trim($_POST['correo'] ?? ''));
$biografia = trim($_POST['biografia'] ?? '');
$avatarPreset = trim($_POST['avatar_preset'] ?? '');
$nuevaClave = $_POST['nueva_contrasena'] ?? '';

if (!preg_match('/^[\p{L}\p{N}_-]{3,40}$/u', $nombre)) {
    header('Location: perfil.php?estado=nombre_invalido'); exit;
}
if (!filter_var($correo, FILTER_VALIDATE_EMAIL) || strlen($correo) > 190) {
    header('Location: perfil.php?estado=correo_invalido'); exit;
}
if ($nuevaClave !== '' && strlen($nuevaClave) < 8) {
    header('Location: perfil.php?estado=clave_corta'); exit;
}

$stmtCur = $pdo->prepare('SELECT avatar FROM usuarios WHERE id = ?');
$stmtCur->execute([$uid]);
$avatarFinal = $stmtCur->fetchColumn() ?: 'default_1';

if (in_array($avatarPreset, ['default_1', 'default_2', 'default_3', 'default_4'], true)) {
    $avatarFinal = $avatarPreset;
}

// Procesar carga de imagen si se adjuntó
if (isset($_FILES['avatar_archivo']) && $_FILES['avatar_archivo']['error'] === UPLOAD_ERR_OK) {
    $tmp = $_FILES['avatar_archivo']['tmp_name'];
    $size = $_FILES['avatar_archivo']['size'];
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($tmp);
    $extMap = ['image/png' => 'png', 'image/jpeg' => 'jpg', 'image/webp' => 'webp'];

    if (!isset($extMap[$mime]) || $size > 2 * 1024 * 1024) {
        header('Location: perfil.php?estado=avatar_invalido'); exit;
    }

    $uploadDir = __DIR__ . '/uploads/avatars';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    $fileName = 'user_' . $uid . '_' . time() . '.' . $extMap[$mime];
    if (move_uploaded_file($tmp, $uploadDir . '/' . $fileName)) {
        $avatarFinal = 'uploads/avatars/' . $fileName;
    }
}

try {
    if ($nuevaClave !== '') {
        $hash = password_hash($nuevaClave, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('UPDATE usuarios SET nombre_usuario = ?, correo = ?, biografia = ?, avatar = ?, contrasena_hash = ? WHERE id = ?');
        $stmt->execute([$nombre, $correo, $biografia !== '' ? $biografia : null, $avatarFinal, $hash, $uid]);
    } else {
        $stmt = $pdo->prepare('UPDATE usuarios SET nombre_usuario = ?, correo = ?, biografia = ?, avatar = ? WHERE id = ?');
        $stmt->execute([$nombre, $correo, $biografia !== '' ? $biografia : null, $avatarFinal, $uid]);
    }

    $_SESSION['nombre_usuario'] = $nombre;
    $_SESSION['avatar'] = $avatarFinal;
    header('Location: perfil.php?estado=actualizado');
    exit;
} catch (PDOException $e) {
    if ($e->getCode() === '23000') {
        header('Location: perfil.php?estado=duplicado'); exit;
    }
    error_log('Error al actualizar perfil: ' . $e->getMessage());
    header('Location: perfil.php?estado=error'); exit;
}
