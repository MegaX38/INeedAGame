<?php
$pageTitle = 'Reportes y Soporte | INeedAGame';
require __DIR__ . '/includes/header.php';

$tipoObjPre = $_GET['tipo_objetivo'] ?? 'general';
$objIdPre = isset($_GET['objetivo_id']) ? (int)$_GET['objetivo_id'] : '';
$refPre = trim($_GET['ref'] ?? '');
?>
<main>
  <?php
  $mensajes = [
    'enviado' => 'Tu solicitud/reporte fue guardado correctamente y será revisado por moderación.',
    'datos_invalidos' => 'Revisa el tipo de solicitud y la descripción.',
    'correo_invalido' => 'El correo de contacto no parece válido.',
    'contenido_largo' => 'El campo de contenido relacionado es demasiado largo.',
    'error' => 'No fue posible guardar la solicitud. Intenta de nuevo.'
  ];
  $estado = $_GET['estado'] ?? '';
  if (isset($mensajes[$estado])) {
      echo '<div class="panel form-message" role="status">' . htmlspecialchars($mensajes[$estado], ENT_QUOTES, 'UTF-8') . '</div>';
  }
  ?>
  <section class="hero reports-hero">
    <div>
      <span class="tag">RF-0011 · CENTRO DE AYUDA Y MODERACIÓN</span>
      <h1>Reportes y soporte</h1>
      <p>Ayúdanos a mantener INeedAGame como un espacio seguro. Envía reportes de contenido, sugerencias o problemas técnicos.</p>
      <?php if ($usuarioLogueado && in_array($rolUsuario, ['moderador', 'admin'], true)): ?>
        <a class="btn primary" href="moderacion.php">Ir al Panel de Moderación</a>
      <?php endif; ?>
    </div>
    <div class="hero-card">
      <span class="tag">COMUNIDAD SEGURA</span>
      <h2>Tu opinión importa.</h2>
      <p>Todas las solicitudes son gestionadas por el equipo de moderación de INeedAGame.</p>
    </div>
  </section>

  <section class="section">
    <div class="two-columns">
      <div>
        <span class="tag">CONTACTO</span>
        <h2>¿Necesitas ayuda?</h2>
        <p>Selecciona el tipo de solicitud y describe lo sucedido con la mayor claridad posible.</p>
        <div class="panel report-info">
          <h3>Tipos de solicitud</h3>
          <p><b>Reporte de contenido:</b> comentarios, artículos o perfiles inapropiados.</p>
          <p><b>Sugerencia:</b> ideas para mejorar la plataforma.</p>
          <p><b>Problema técnico:</b> errores de navegación o visualización.</p>
        </div>
      </div>

      <form class="panel form" action="guardar_reporte.php" method="post" id="solicitud">
        <h2>Enviar solicitud</h2>
        <input type="hidden" name="tipo_objetivo" value="<?php echo htmlspecialchars($tipoObjPre); ?>">
        <input type="hidden" name="objetivo_id" value="<?php echo htmlspecialchars((string)$objIdPre); ?>">

        <label>Tipo de solicitud
          <select name="tipo" required>
            <option value="">Selecciona una opción</option>
            <option value="Reporte de contenido" <?php echo $tipoObjPre !== 'general' ? 'selected' : ''; ?>>Reporte de contenido</option>
            <option value="Sugerencia">Sugerencia</option>
            <option value="Problema técnico">Problema técnico</option>
            <option value="Consulta general">Consulta general</option>
          </select>
        </label>
        <label>Correo de contacto (opcional)<input type="email" name="correo" placeholder="nombre@correo.com"></label>
        <label>Videojuego o contenido relacionado (opcional)<input type="text" name="contenido" value="<?php echo htmlspecialchars($refPre); ?>" placeholder="Nombre del juego, artículo o comentario"></label>
        <label>Descripción<textarea name="mensaje" rows="6" placeholder="Describe tu solicitud..." required></textarea></label>
        <button class="btn primary" type="submit">Enviar solicitud</button>
      </form>
    </div>
  </section>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
