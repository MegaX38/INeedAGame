<?php
require __DIR__ . '/config/db.php';

$artId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $pdo->prepare("
    SELECT a.*, u.nombre_usuario, u.avatar, v.titulo AS juego_titulo,
           (SELECT ROUND(AVG(cal.valor), 1) FROM calificaciones cal WHERE cal.tipo_objetivo = 'articulo' AND cal.objetivo_id = a.id) AS promedio_cal,
           (SELECT COUNT(*) FROM calificaciones cal WHERE cal.tipo_objetivo = 'articulo' AND cal.objetivo_id = a.id) AS total_votos
    FROM articulos a
    JOIN usuarios u ON u.id = a.usuario_id
    LEFT JOIN videojuegos v ON v.id = a.juego_id
    WHERE a.id = ? AND a.estado = 'publicado' LIMIT 1
");
$stmt->execute([$artId]);
$art = $stmt->fetch();

if (!$art) {
    header('Location: articulos.php');
    exit;
}

$pageTitle = $art['titulo'] . ' | INeedAGame';
require __DIR__ . '/includes/header.php';

$mensajes = [
    'publicado' => '¡Tu artículo ha sido publicado en la comunidad!',
    'voto_ok' => '¡Calificación registrada! El promedio se ha actualizado.',
    'voto_duplicado' => 'Ya habías calificado este artículo.'
];
$estado = $_GET['estado'] ?? '';
?>

<main class="section">
  <?php if (isset($mensajes[$estado])): ?>
    <div class="panel form-message" style="margin-bottom:24px;" role="status"><?php echo htmlspecialchars($mensajes[$estado]); ?></div>
  <?php endif; ?>

  <article class="panel" style="max-width:860px;margin:auto;">
    <a href="articulos.php" style="color:#22d3ee;font-size:.9rem;">← Volver a artículos</a>
    <div class="row" style="margin-top:16px;">
      <span class="tag"><?php echo htmlspecialchars(mb_strtoupper($art['categoria'])); ?></span>
      <span style="font-size:1.1rem;">★ <?php echo $art['promedio_cal'] ? $art['promedio_cal'] . ' / 5 (' . $art['total_votos'] . ' votos)' : 'Sin calificaciones aún'; ?></span>
    </div>

    <h1 style="font-size:2.3rem;margin:12px 0;"><?php echo htmlspecialchars($art['titulo']); ?></h1>

    <div class="user" style="margin-bottom:22px;padding-bottom:16px;border-bottom:1px solid #ffffff18;">
      <?php echo renderAvatar($art['avatar'], $art['nombre_usuario']); ?>
      <div>
        <b><?php echo htmlspecialchars($art['nombre_usuario']); ?></b>
        <small>Publicado el <?php echo date('d/m/Y H:i', strtotime($art['fecha_creacion'])); ?><?php echo $art['juego_titulo'] ? ' · Sobre: ' . htmlspecialchars($art['juego_titulo']) : ''; ?></small>
      </div>
      <a class="link" style="color:#f87171;font-size:.85rem;" href="reportes.php?tipo_objetivo=articulo&objetivo_id=<?php echo (int)$art['id']; ?>&ref=<?php echo urlencode('Artículo #' . $art['id'] . ': ' . $art['titulo']); ?>">Reportar artículo</a>
    </div>

    <div style="color:#e2e0f0;font-size:1.05rem;line-height:1.8;white-space:pre-line;margin-bottom:30px;">
      <?php echo htmlspecialchars($art['contenido']); ?>
    </div>

    <!-- RF-0010: Calificación de artículos -->
    <div class="notice">
      <h3>Califica este artículo (RF-0010)</h3>
      <?php if ($usuarioLogueado): ?>
        <form action="calificar.php" method="post" style="display:flex;gap:12px;align-items:center;flex-wrap:wrap;margin-top:12px;">
          <input type="hidden" name="tipo_objetivo" value="articulo">
          <input type="hidden" name="objetivo_id" value="<?php echo (int)$art['id']; ?>">
          <input type="hidden" name="redirect" value="articulo.php?id=<?php echo (int)$art['id']; ?>">
          <select name="valor" style="padding:10px;background:#0b0b14;color:white;border:1px solid #ffffff22;border-radius:8px;">
            <option value="5">★★★★★ (5 estrellas)</option>
            <option value="4">★★★★☆ (4 estrellas)</option>
            <option value="3">★★★☆☆ (3 estrellas)</option>
            <option value="2">★★☆☆☆ (2 estrellas)</option>
            <option value="1">★☆☆☆☆ (1 estrella)</option>
          </select>
          <button class="btn primary" type="submit">Enviar calificación</button>
        </form>
      <?php else: ?>
        <p>Debes <a href="acceso.php#login" style="color:#22d3ee;">iniciar sesión</a> para calificar este artículo.</p>
      <?php endif; ?>
    </div>
  </article>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>
