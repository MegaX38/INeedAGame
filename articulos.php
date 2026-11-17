<?php
require __DIR__ . '/config/db.php';
$pageTitle = 'Artículos y Blog | INeedAGame';
require __DIR__ . '/includes/header.php';

$articulos = $pdo->query("
    SELECT a.*, u.nombre_usuario, u.avatar, v.titulo AS juego_titulo,
           (SELECT ROUND(AVG(cal.valor), 1) FROM calificaciones cal WHERE cal.tipo_objetivo = 'articulo' AND cal.objetivo_id = a.id) AS promedio_cal,
           (SELECT COUNT(*) FROM calificaciones cal WHERE cal.tipo_objetivo = 'articulo' AND cal.objetivo_id = a.id) AS total_votos
    FROM articulos a
    JOIN usuarios u ON u.id = a.usuario_id
    LEFT JOIN videojuegos v ON v.id = a.juego_id
    WHERE a.estado = 'publicado'
    ORDER BY a.fecha_creacion DESC
")->fetchAll();
?>

<main class="section">
  <div class="heading">
    <div>
      <span class="tag">RF-0009 · COMUNIDAD Y BLOG</span>
      <h2>Artículos publicados</h2>
    </div>
    <?php if ($usuarioLogueado): ?>
      <a class="btn primary" href="crear_articulo.php">+ Escribir nuevo artículo</a>
    <?php else: ?>
      <a class="btn secondary" href="acceso.php#login">Inicia sesión para publicar</a>
    <?php endif; ?>
  </div>

  <div class="cards">
    <?php foreach ($articulos as $art): ?>
      <article class="panel" style="display:flex;flex-direction:column;justify-content:space-between;">
        <div>
          <div class="row" style="margin-bottom:10px;">
            <span class="tag"><?php echo htmlspecialchars(mb_strtoupper($art['categoria'])); ?></span>
            <span>★ <?php echo $art['promedio_cal'] ? $art['promedio_cal'] . '/5 (' . $art['total_votos'] . ')' : 'Sin calificar'; ?></span>
          </div>
          <h3><?php echo htmlspecialchars($art['titulo']); ?></h3>
          <?php if ($art['juego_titulo']): ?>
            <small class="small" style="display:block;margin:6px 0;">Juego relacionado: <?php echo htmlspecialchars($art['juego_titulo']); ?></small>
          <?php endif; ?>
          <p style="border:0;color:#aaa7c3;font-size:.9rem;"><?php echo htmlspecialchars(mb_strimwidth($art['contenido'], 0, 170, '...')); ?></p>
        </div>
        <div style="margin-top:16px;padding-top:12px;border-top:1px solid #ffffff18;">
          <div class="row">
            <small style="color:#aaa7c3;">Por <b><?php echo htmlspecialchars($art['nombre_usuario']); ?></b> · <?php echo date('d/m/Y', strtotime($art['fecha_creacion'])); ?></small>
            <a class="btn primary" style="padding:7px 12px;" href="articulo.php?id=<?php echo (int)$art['id']; ?>">Leer artículo</a>
          </div>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>
