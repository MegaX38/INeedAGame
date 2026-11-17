<?php
require __DIR__ . '/config/db.php';

$juegoId = isset($_GET['id']) ? (int)$_GET['id'] : 1;
$plataformaFiltro = trim($_GET['plataforma'] ?? '');

$stmt = $pdo->prepare('SELECT * FROM videojuegos WHERE id = ? LIMIT 1');
$stmt->execute([$juegoId]);
$juego = $stmt->fetch();

if (!$juego) {
    header('Location: index.php');
    exit;
}

// RF-0006: Especificaciones técnicas
$stmtSpec = $pdo->prepare('SELECT * FROM especificaciones_juego WHERE juego_id = ? LIMIT 1');
$stmtSpec->execute([$juegoId]);
$specs = $stmtSpec->fetch();

// RF-0007: Precios disponibles (con filtro opcional por plataforma)
if ($plataformaFiltro !== '') {
    $stmtPrecios = $pdo->prepare('SELECT * FROM precios_juego WHERE juego_id = ? AND plataforma LIKE ? ORDER BY precio ASC');
    $stmtPrecios->execute([$juegoId, "%{$plataformaFiltro}%"]);
} else {
    $stmtPrecios = $pdo->prepare('SELECT * FROM precios_juego WHERE juego_id = ? ORDER BY precio ASC');
    $stmtPrecios->execute([$juegoId]);
}
$precios = $stmtPrecios->fetchAll();

// RF-0008 y RF-0010: Comentarios del videojuego + conteo de votos útiles
$stmtCom = $pdo->prepare("
    SELECT c.*, u.nombre_usuario, u.avatar, u.rol,
           (SELECT COUNT(*) FROM calificaciones cal WHERE cal.tipo_objetivo = 'comentario' AND cal.objetivo_id = c.id) AS votos_utiles
    FROM comentarios c
    JOIN usuarios u ON u.id = c.usuario_id
    WHERE c.juego_id = ? AND c.estado = 'visible'
    ORDER BY c.fecha_creacion DESC
");
$stmtCom->execute([$juegoId]);
$comentarios = $stmtCom->fetchAll();

$pageTitle = $juego['titulo'] . ' | INeedAGame';
require __DIR__ . '/includes/header.php';

$mensajes = [
    'comentario_ok' => 'Tu comentario fue publicado correctamente.',
    'comentario_invalido' => 'Escribe un comentario válido (entre 3 y 1500 caracteres).',
    'voto_ok' => '¡Gracias! Tu calificación fue registrada.',
    'voto_duplicado' => 'Ya habías calificado este contenido anteriormente.'
];
$estado = $_GET['estado'] ?? '';
?>

<main>
<?php if (isset($mensajes[$estado])): ?>
  <div class="panel form-message" role="status"><?php echo htmlspecialchars($mensajes[$estado]); ?></div>
<?php endif; ?>

<!-- RF-0005: Consulta de información detallada del videojuego -->
<section class="section">
  <a href="index.php#juegos" style="color:#22d3ee;font-size:.9rem;">← Volver al catálogo</a>
  <div class="two-columns" style="margin-top:20px;">
    <div>
      <div class="cover <?php echo htmlspecialchars($juego['cover_clase']); ?>" style="border-radius:18px;min-height:260px;margin-bottom:22px;">
        <?php echo htmlspecialchars(mb_strtoupper($juego['titulo'])); ?>
      </div>
      <span class="tag">RF-0005 · FICHA DEL VIDEOJUEGO</span>
      <h1 style="font-size:2.6rem;margin:10px 0;"><?php echo htmlspecialchars($juego['titulo']); ?></h1>
      <p style="color:#aaa7c3;font-size:1.02rem;"><?php echo nl2br(htmlspecialchars($juego['sinopsis'])); ?></p>
    </div>

    <div class="panel">
      <h3>Información general</h3>
      <p><b>Desarrollador:</b> <?php echo htmlspecialchars($juego['desarrollador']); ?></p>
      <p><b>Género:</b> <?php echo htmlspecialchars($juego['genero']); ?></p>
      <p><b>Plataformas:</b> <?php echo htmlspecialchars($juego['plataformas']); ?></p>
      <p><b>Fecha de lanzamiento:</b> <?php echo date('d/m/Y', strtotime($juego['fecha_lanzamiento'])); ?></p>
      <p><b>Jugadores estimados:</b> <?php echo htmlspecialchars($juego['jugadores_estimados']); ?></p>
      <p><b>Popularidad:</b> <?php echo htmlspecialchars($juego['popularidad']); ?></p>
      <p style="border:0;"><b>Mods:</b> <?php echo htmlspecialchars($juego['mods_disponibles']); ?></p>
    </div>
  </div>
</section>

<!-- RF-0006: Consulta de especificaciones y requisitos -->
<section id="especificaciones" class="section alternate">
  <div class="heading">
    <div>
      <span class="tag">RF-0006 · REQUISITOS DE SISTEMA</span>
      <h2>Especificaciones técnicas</h2>
    </div>
  </div>

  <?php if ($specs): ?>
    <div class="table-wrap panel">
      <table class="data-table">
        <thead>
          <tr>
            <th>Componente</th>
            <th>Requisitos Mínimos</th>
            <th>Requisitos Recomendados</th>
          </tr>
        </thead>
        <tbody>
          <tr><td><b>Sistema Operativo</b></td><td><?php echo htmlspecialchars($specs['so_min']); ?></td><td><?php echo htmlspecialchars($specs['so_rec']); ?></td></tr>
          <tr><td><b>Procesador (CPU)</b></td><td><?php echo htmlspecialchars($specs['cpu_min']); ?></td><td><?php echo htmlspecialchars($specs['cpu_rec']); ?></td></tr>
          <tr><td><b>Memoria RAM</b></td><td><?php echo htmlspecialchars($specs['ram_min']); ?></td><td><?php echo htmlspecialchars($specs['ram_rec']); ?></td></tr>
          <tr><td><b>Tarjeta Gráfica (GPU)</b></td><td><?php echo htmlspecialchars($specs['gpu_min']); ?></td><td><?php echo htmlspecialchars($specs['gpu_rec']); ?></td></tr>
          <tr><td><b>Almacenamiento</b></td><td><?php echo htmlspecialchars($specs['almacenamiento_min']); ?></td><td><?php echo htmlspecialchars($specs['almacenamiento_rec']); ?></td></tr>
        </tbody>
      </table>
    </div>
  <?php else: ?>
    <div class="panel"><p>No hay especificaciones técnicas registradas para este título.</p></div>
  <?php endif; ?>
</section>

<!-- RF-0007: Consulta de precios -->
<section id="precios" class="section">
  <div class="heading">
    <div>
      <span class="tag">RF-0007 · COMPARADOR DE TIENDAS</span>
      <h2>Precios disponibles</h2>
    </div>
    <form method="get" action="juego.php#precios" style="display:flex;gap:8px;">
      <input type="hidden" name="id" value="<?php echo $juegoId; ?>">
      <select name="plataforma" style="padding:8px 12px;background:#141426;color:white;border:1px solid #ffffff22;border-radius:8px;">
        <option value="">Todas las plataformas</option>
        <option value="PC" <?php echo $plataformaFiltro === 'PC' ? 'selected' : ''; ?>>PC</option>
        <option value="Nintendo Switch" <?php echo $plataformaFiltro === 'Nintendo Switch' ? 'selected' : ''; ?>>Nintendo Switch</option>
        <option value="PS5" <?php echo $plataformaFiltro === 'PS5' ? 'selected' : ''; ?>>PS5</option>
        <option value="Xbox" <?php echo $plataformaFiltro === 'Xbox' ? 'selected' : ''; ?>>Xbox</option>
      </select>
      <button class="btn secondary" type="submit">Filtrar</button>
    </form>
  </div>

  <?php if (empty($precios)): ?>
    <div class="panel"><p>No hay precios registrados para el filtro seleccionado.</p></div>
  <?php else: ?>
    <div class="table-wrap panel">
      <table class="data-table">
        <thead>
          <tr>
            <th>Tienda</th>
            <th>Plataforma</th>
            <th>Descuento</th>
            <th>Precio Final</th>
            <th>Enlace</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($precios as $p): ?>
            <tr>
              <td><b><?php echo htmlspecialchars($p['tienda']); ?></b></td>
              <td><?php echo htmlspecialchars($p['plataforma']); ?></td>
              <td><?php echo (int)$p['descuento_pct'] > 0 ? '<span class="badge-green">-' . (int)$p['descuento_pct'] . '%</span>' : '—'; ?></td>
              <td><b><?php echo (float)$p['precio'] == 0.0 ? 'Gratis' : '$' . number_format((float)$p['precio'], 2) . ' USD'; ?></b></td>
              <td><a class="link" href="<?php echo htmlspecialchars($p['url_tienda'] ?: '#'); ?>" target="_blank" rel="noopener">Ir a tienda ↗</a></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</section>

<!-- RF-0008 y RF-0010: Comentarios y calificación -->
<section id="comentarios" class="section alternate">
  <div class="heading">
    <div>
      <span class="tag">RF-0008 & RF-0010 · OPINIONES</span>
      <h2>Comentarios de jugadores (<?php echo count($comentarios); ?>)</h2>
    </div>
  </div>

  <?php if ($usuarioLogueado): ?>
    <form class="panel form" action="guardar_comentario.php" method="post" style="margin-bottom:30px;">
      <h3>Publicar un comentario</h3>
      <input type="hidden" name="juego_id" value="<?php echo $juegoId; ?>">
      <label>Calificación del juego
        <select name="puntuacion" required>
          <option value="5">★★★★★ (5 - Excelente)</option>
          <option value="4">★★★★☆ (4 - Muy bueno)</option>
          <option value="3">★★★☆☆ (3 - Bueno)</option>
          <option value="2">★★☆☆☆ (2 - Regular)</option>
          <option value="1">★☆☆☆☆ (1 - Malo)</option>
        </select>
      </label>
      <label>Tu opinión
        <textarea name="contenido" rows="3" placeholder="Comparte tu experiencia con este videojuego..." required></textarea>
      </label>
      <button class="btn primary" type="submit">Publicar comentario</button>
    </form>
  <?php else: ?>
    <div class="notice" style="margin-bottom:30px;">
      <h3>Inicia sesión para comentar</h3>
      <p>Solo los usuarios registrados pueden publicar opiniones y calificar comentarios.</p>
      <a class="btn primary" href="acceso.php#login">Iniciar sesión</a>
    </div>
  <?php endif; ?>

  <div class="comments">
    <?php if (empty($comentarios)): ?>
      <div class="panel"><p>Sé el primero en comentar este videojuego.</p></div>
    <?php else: ?>
      <?php foreach ($comentarios as $c): ?>
        <article class="panel">
          <div class="user">
            <?php echo renderAvatar($c['avatar'], $c['nombre_usuario']); ?>
            <div>
              <b><?php echo htmlspecialchars($c['nombre_usuario']); ?></b>
              <small><?php echo date('d/m/Y H:i', strtotime($c['fecha_creacion'])); ?></small>
            </div>
            <span>★ <?php echo number_format((float)$c['puntuacion'], 1); ?></span>
          </div>
          <p style="border:0;margin:12px 0;"><?php echo nl2br(htmlspecialchars($c['contenido'])); ?></p>
          <div class="row" style="padding-top:10px;border-top:1px solid #ffffff18;">
            <form action="calificar.php" method="post" style="display:inline;">
              <input type="hidden" name="tipo_objetivo" value="comentario">
              <input type="hidden" name="objetivo_id" value="<?php echo (int)$c['id']; ?>">
              <input type="hidden" name="valor" value="1">
              <input type="hidden" name="redirect" value="juego.php?id=<?php echo $juegoId; ?>#comentarios">
              <button class="link" type="submit">👍 Útil (<?php echo (int)$c['votos_utiles']; ?>)</button>
            </form>
            <a class="link" style="color:#f87171;font-size:.82rem;" href="reportes.php?tipo_objetivo=comentario&objetivo_id=<?php echo (int)$c['id']; ?>&ref=<?php echo urlencode('Comentario #' . $c['id'] . ' en ' . $juego['titulo']); ?>">Reportar</a>
          </div>
        </article>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</section>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>
