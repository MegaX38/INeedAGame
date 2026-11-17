<?php
$pageTitle = 'Acceso y Registro | INeedAGame';
require __DIR__ . '/includes/header.php';
?>
<main class="auth-page">
  <?php
  $mensajes = [
    'nombre_invalido' => 'El usuario debe tener entre 3 y 40 caracteres y usar letras, números, guion o guion bajo.',
    'correo_invalido' => 'Escribe un correo electrónico válido.',
    'clave_corta' => 'La contraseña debe tener al menos 8 caracteres.',
    'duplicado' => 'Ese nombre de usuario o correo ya está registrado.',
    'error' => 'No fue posible completar la operación. Intenta de nuevo.',
    'credenciales' => 'Usuario/correo o contraseña incorrectos.',
    'accion_invalida' => 'La solicitud no es válida.',
    'requiere_login' => 'Debes iniciar sesión para acceder a esa función.'
  ];
  $estado = $_GET['estado'] ?? '';
  if (isset($mensajes[$estado])) {
      echo '<div class="panel form-message" role="alert">' . htmlspecialchars($mensajes[$estado], ENT_QUOTES, 'UTF-8') . '</div>';
  }
  ?>
  <section id="login" class="section">
    <div class="panel auth-panel">
      <span class="tag">ACCESO (RF-0001)</span>
      <h2>Iniciar sesión</h2>
      <p>Ingresa con tu nombre de usuario o correo electrónico.</p>
      <form class="form" action="auth.php" method="post">
        <input type="hidden" name="accion" value="login">
        <label>Usuario o correo electrónico<input type="text" name="identificador" placeholder="gamer123 o nombre@correo.com" required></label>
        <label>Contraseña<input type="password" name="contrasena" placeholder="Tu contraseña" required></label>
        <button class="btn primary" type="submit">Ingresar</button>
      </form>
      <p class="switch-auth">¿No tienes cuenta? <a href="#registro">Crear cuenta</a></p>
      <small class="form-note">Cuentas de prueba preinstaladas: <b>gamer123</b> / <code>Pass2026!</code> · Moderador: <b>admin_mod</b> / <code>Admin2026!</code></small>
    </div>
  </section>

  <section id="registro" class="section alternate auth-alternate">
    <div class="panel auth-panel">
      <span class="tag">ÚNETE A LA COMUNIDAD (RF-0002)</span>
      <h2>Crear cuenta</h2>
      <p>Regístrate para gestionar tu perfil, comentar, publicar artículos y calificar.</p>
      <form class="form" action="auth.php" method="post">
        <input type="hidden" name="accion" value="registro">
        <label>Nombre de usuario<input type="text" name="usuario" placeholder="Tu nombre gamer" minlength="3" maxlength="40" required></label>
        <label>Correo electrónico<input type="email" name="correo" placeholder="nombre@correo.com" required></label>
        <label>Contraseña<input type="password" name="contrasena" placeholder="Mínimo 8 caracteres" minlength="8" required></label>
        <button class="btn primary" type="submit">Registrarme</button>
      </form>
      <p class="switch-auth">¿Ya tienes cuenta? <a href="#login">Iniciar sesión</a></p>
      <small class="form-note">La contraseña se almacena cifrada con bcrypt (<code>password_hash</code>).</small>
    </div>
  </section>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
