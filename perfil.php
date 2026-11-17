<?php
require __DIR__ . '/config/db.php';
$pageTitle = 'Mi Perfil | INeedAGame';
require __DIR__ . '/includes/header.php';

if (!$usuarioLogueado) {
    header('Location: acceso.php?estado=requiere_login#login');
    exit;
}

$stmt = $pdo->prepare('SELECT id, nombre_usuario, correo, avatar, biografia, rol, fecha_registro FROM usuarios WHERE id = ? LIMIT 1');
$stmt->execute([$_SESSION['usuario_id']]);
$perfil = $stmt->fetch();

$mensajes = [
    'actualizado' => 'Tu perfil ha sido actualizado correctamente.',
    'nombre_invalido' => 'El nombre de usuario debe tener entre 3 y 40 caracteres válidos.',
    'correo_invalido' => 'El correo electrónico no es válido.',
    'duplicado' => 'Ese nombre de usuario o correo ya está en uso por otra cuenta.',
    'avatar_invalido' => 'Formato de imagen no permitido. Usa PNG, JPG o WEBP (máx. 2MB).',
    'clave_corta' => 'La nueva contraseña debe tener al menos 8 caracteres.',
    'error' => 'Ocurrió un error al actualizar tu perfil.'
];
$estado = $_GET['estado'] ?? '';
?>

<main class="section">
  <?php if (isset($mensajes[$estado])): ?>
    <div class="panel form-message" style="margin-bottom:24px;" role="status"><?php echo htmlspecialchars($mensajes[$estado]); ?></div>
  <?php endif; ?>

  <div class="two-columns">
    <div class="panel">
      <span class="tag">MI CUENTA · <?php echo htmlspecialchars(strtoupper($perfil['rol'])); ?></span>
      <div style="display:flex;align-items:center;gap:16px;margin:20px 0;">
        <?php echo renderAvatar($perfil['avatar'], $perfil['nombre_usuario'], 'avatar-lg'); ?>
        <div>
          <h2 style="font-size:1.6rem;margin:0;"><?php echo htmlspecialchars($perfil['nombre_usuario']); ?></h2>
          <small style="color:#aaa7c3;"><?php echo htmlspecialchars($perfil['correo']); ?></small><br>
          <small style="color:#22d3ee;">Miembro desde: <?php echo date('d/m/Y', strtotime($perfil['fecha_registro'])); ?></small>
        </div>
      </div>
      <p><b>Biografía:</b> <?php echo htmlspecialchars($perfil['biografia'] ?: 'Aún no has escrito una biografía.'); ?></p>
    </div>

    <!-- RF-0003: Formulario de actualización de perfil -->
    <form class="panel form" action="actualizar_perfil.php" method="post" enctype="multipart/form-data">
      <span class="tag">RF-0003 · GESTIÓN DEL PERFIL</span>
      <h2>Editar perfil y avatar</h2>

      <label>Nombre de usuario
        <input type="text" name="nombre_usuario" value="<?php echo htmlspecialchars($perfil['nombre_usuario']); ?>" minlength="3" maxlength="40" required>
      </label>

      <label>Correo electrónico
        <input type="email" name="correo" value="<?php echo htmlspecialchars($perfil['correo']); ?>" required>
      </label>

      <label>Biografía gamer
        <textarea name="biografia" rows="3" maxlength="300" placeholder="Cuéntale a la comunidad qué géneros juegas..."><?php echo htmlspecialchars($perfil['biografia'] ?? ''); ?></textarea>
      </label>

      <label>Seleccionar avatar predeterminado
        <select name="avatar_preset">
          <option value="">Mantener avatar actual</option>
          <option value="default_1" <?php echo $perfil['avatar'] === 'default_1' ? 'selected' : ''; ?>>Estilo Morado / Cian</option>
          <option value="default_2" <?php echo $perfil['avatar'] === 'default_2' ? 'selected' : ''; ?>>Estilo Neón Rosa</option>
          <option value="default_3" <?php echo $perfil['avatar'] === 'default_3' ? 'selected' : ''; ?>>Estilo Cian Eléctrico</option>
          <option value="default_4" <?php echo $perfil['avatar'] === 'default_4' ? 'selected' : ''; ?>>Estilo Dorado Legendario</option>
        </select>
      </label>

      <label>O subir imagen de avatar personalizada (PNG/JPG/WEBP, máx. 2MB)
        <input type="file" name="avatar_archivo" accept="image/png,image/jpeg,image/webp">
      </label>

      <label>Nueva contraseña (dejar en blanco para no cambiarla)
        <input type="password" name="nueva_contrasena" minlength="8" placeholder="Mínimo 8 caracteres">
      </label>

      <button class="btn primary" type="submit">Guardar cambios</button>
    </form>
  </div>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>
