<?php
require __DIR__ . '/config/db.php';
$pageTitle = 'Panel de Moderación | INeedAGame';
require __DIR__ . '/includes/header.php';

if (!$usuarioLogueado || !in_array($rolUsuario, ['moderador', 'admin'], true)) {
    http_response_code(403);
    echo '<main class="section"><div class="panel"><h2>Acceso denegado (403)</h2><p>Esta sección es exclusiva para usuarios con rol Moderador o Administrador.</p><a class="btn primary" href="index.php">Volver al inicio</a></div></main>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

$reportes = $pdo->query("
    SELECT r.*, u.nombre_usuario AS reportante, m.nombre_usuario AS moderador_nombre
    FROM reportes r
    LEFT JOIN usuarios u ON u.id = r.usuario_id
    LEFT JOIN usuarios m ON m.id = r.moderado_por
    ORDER BY FIELD(r.estado, 'pendiente', 'en_revision', 'resuelto', 'rechazado'), r.fecha_creacion DESC
")->fetchAll();

$estado = $_GET['estado'] ?? '';
?>

<main class="section">
  <?php if ($estado === 'actualizado'): ?>
    <div class="panel form-message" style="margin-bottom:24px;" role="status">Acción de moderación aplicada y registrada correctamente.</div>
  <?php endif; ?>

  <div class="heading">
    <div>
      <span class="tag">RF-0011 · ADMINISTRACIÓN Y AUDITORÍA</span>
      <h2>Panel de Moderación y Reportes</h2>
    </div>
  </div>

  <div class="table-wrap panel">
    <table class="data-table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Tipo / Origen</th>
          <th>Contenido relacionado</th>
          <th>Descripción</th>
          <th>Estado</th>
          <th>Acciones de Moderación</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($reportes as $rep): ?>
          <tr>
            <td><b>#<?php echo (int)$rep['id']; ?></b></td>
            <td>
              <b><?php echo htmlspecialchars($rep['tipo']); ?></b><br>
              <small style="color:#aaa7c3;">Por: <?php echo htmlspecialchars($rep['reportante'] ?: ($rep['correo_contacto'] ?: 'Anónimo')); ?></small>
            </td>
            <td><?php echo htmlspecialchars($rep['contenido_relacionado'] ?: 'General'); ?></td>
            <td style="max-width:280px;"><?php echo htmlspecialchars($rep['descripcion']); ?></td>
            <td>
              <span class="status-badge status-<?php echo htmlspecialchars($rep['estado']); ?>">
                <?php echo htmlspecialchars(strtoupper($rep['estado'])); ?>
              </span>
            </td>
            <td>
              <form action="procesar_moderacion.php" method="post" style="display:flex;gap:6px;flex-wrap:wrap;">
                <input type="hidden" name="reporte_id" value="<?php echo (int)$rep['id']; ?>">
                <button class="btn primary" style="padding:6px 10px;font-size:.78rem;" type="submit" name="accion" value="aprobar">Aprobar / Resolver</button>
                <button class="btn secondary" style="padding:6px 10px;font-size:.78rem;" type="submit" name="accion" value="rechazar">Rechazar</button>
                <?php if (in_array($rep['tipo_objetivo'], ['comentario', 'articulo'], true) && $rep['objetivo_id']): ?>
                  <button class="btn danger" style="padding:6px 10px;font-size:.78rem;" type="submit" name="accion" value="ocultar_contenido">Ocultar contenido</button>
                <?php endif; ?>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>
