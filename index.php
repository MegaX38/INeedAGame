<?php
require __DIR__ . '/config/db.php';
$pageTitle = 'Inicio | INeedAGame';
require __DIR__ . '/includes/header.php';

// RF-0004: Búsqueda de videojuegos por título, género o plataforma
$busqueda = trim($_GET['q'] ?? '');
$generoFiltro = trim($_GET['genero'] ?? '');

$sql = "SELECT v.*,
        (SELECT MIN(p.precio) FROM precios_juego p WHERE p.juego_id = v.id) AS precio_min,
        (SELECT ROUND(AVG(c.puntuacion), 1) FROM comentarios c WHERE c.juego_id = v.id AND c.estado = 'visible') AS promedio_estrellas
        FROM videojuegos v WHERE 1=1";
$params = [];

if ($busqueda !== '') {
    $sql .= " AND (v.titulo LIKE ? OR v.genero LIKE ? OR v.plataformas LIKE ?)";
    $like = "%{$busqueda}%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}
if ($generoFiltro !== '') {
    $sql .= " AND v.genero LIKE ?";
    $params[] = "%{$generoFiltro}%";
}
$sql .= " ORDER BY v.id ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$juegos = $stmt->fetchAll();

// Últimos artículos publicados (RF-0009)
$articulosRecientes = $pdo->query("
    SELECT a.*, u.nombre_usuario, u.avatar, v.titulo AS juego_titulo,
           (SELECT ROUND(AVG(cal.valor), 1) FROM calificaciones cal WHERE cal.tipo_objetivo = 'articulo' AND cal.objetivo_id = a.id) AS promedio_cal,
           (SELECT COUNT(*) FROM calificaciones cal WHERE cal.tipo_objetivo = 'articulo' AND cal.objetivo_id = a.id) AS total_votos
    FROM articulos a
    JOIN usuarios u ON u.id = a.usuario_id
    LEFT JOIN videojuegos v ON v.id = a.juego_id
    WHERE a.estado = 'publicado'
    ORDER BY a.fecha_creacion DESC LIMIT 3
")->fetchAll();

$mensajesEstado = [
    'registro_ok' => '¡Cuenta creada con éxito! Bienvenido a INeedAGame.',
    'login_ok' => 'Sesión iniciada correctamente.',
    'sesion_cerrada' => 'Has cerrado sesión correctamente.'
];
$estado = $_GET['estado'] ?? '';
?>

<main>
<?php if (isset($mensajesEstado[$estado])): ?>
  <div class="panel form-message" role="status"><?php echo htmlspecialchars($mensajesEstado[$estado]); ?></div>
<?php endif; ?>

<section id="inicio" class="hero">
  <div>
    <span class="tag">COMUNIDAD GAMER</span>
    <h1>Encuentra tu próximo juego favorito.</h1>
    <p>Consulta información detallada, especificaciones técnicas, precios comparativos, artículos y opiniones de otros jugadores.</p>
    <!-- RF-0004: Barra de búsqueda -->
    <form class="search" action="index.php#juegos" method="get">
      <input type="search" name="q" value="<?php echo htmlspecialchars($busqueda); ?>" placeholder="Buscar videojuego por nombre, género o plataforma..." aria-label="Buscar videojuego">
      <button class="btn primary" type="submit">Buscar</button>
    </form>
    <?php if ($busqueda !== '' || $generoFiltro !== ''): ?>
      <p style="margin-top:10px;font-size:.85rem;">Mostrando resultados para <b><?php echo htmlspecialchars($busqueda ?: $generoFiltro); ?></b> · <a href="index.php#juegos" style="color:#22d3ee;">Limpiar filtro</a></p>
    <?php endif; ?>
  </div>
  <div class="hero-card">
    <span class="tag">INEEDAGAME · CATÁLOGO & BLOG</span>
    <h2>Descubre. Comparte. Juega.</h2>
    <p>Explora requisitos de sistema, compara precios entre tiendas y publica tus propias reseñas.</p>
  </div>
</section>

<section id="juegos" class="section">
  <div class="heading">
    <div>
      <span class="tag">EXPLORA EL CATÁLOGO</span>
      <h2>Videojuegos (<?php echo count($juegos); ?>)</h2>
    </div>
    <div class="filter-pills">
      <a class="pill <?php echo $generoFiltro === '' ? 'active' : ''; ?>" href="index.php#juegos">Todos</a>
      <a class="pill <?php echo $generoFiltro === 'RPG' ? 'active' : ''; ?>" href="index.php?genero=RPG#juegos">RPG</a>
      <a class="pill <?php echo $generoFiltro === 'Aventura' ? 'active' : ''; ?>" href="index.php?genero=Aventura#juegos">Aventura</a>
      <a class="pill <?php echo $generoFiltro === 'Carreras' ? 'active' : ''; ?>" href="index.php?genero=Carreras#juegos">Carreras</a>
    </div>
  </div>

  <?php if (empty($juegos)): ?>
    <div class="panel">
      <h3>No se encontraron videojuegos</h3>
      <p>No hay coincidencias con "<?php echo htmlspecialchars($busqueda); ?>". Intenta con otro título o <a href="index.php#juegos" style="color:#22d3ee;">mira todo el catálogo</a>.</p>
    </div>
  <?php else: ?>
    <div class="cards">
      <?php foreach ($juegos as $j):
        $precioTxt = ($j['precio_min'] === null) ? 'Consultar' : ((float)$j['precio_min'] == 0.0 ? 'Gratis' : '$' . number_format((float)$j['precio_min'], 2));
        $estrellas = $j['promedio_estrellas'] ? number_format((float)$j['promedio_estrellas'], 1) : '4.8';
      ?>
        <article class="card">
          <div class="cover <?php echo htmlspecialchars($j['cover_clase']); ?>">
            <?php echo htmlspecialchars(mb_strtoupper($j['titulo'])); ?>
          </div>
          <div class="body">
            <span class="small"><?php echo htmlspecialchars($j['genero']); ?> · <?php echo htmlspecialchars($j['plataformas']); ?></span>
            <h3><?php echo htmlspecialchars($j['titulo']); ?></h3>
            <p><?php echo htmlspecialchars(mb_strimwidth($j['sinopsis'], 0, 105, '...')); ?></p>
            <div class="row">
              <b>Desde <?php echo $precioTxt; ?></b>
              <span>★ <?php echo $estrellas; ?></span>
            </div>
            <!-- RF-0005, RF-0006, RF-0007, RF-0008 -->
            <a class="btn primary full" href="juego.php?id=<?php echo (int)$j['id']; ?>">Ver ficha, precios y specs</a>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>

<!-- RF-0009 y RF-0010: Artículos recientes de la comunidad -->
<section id="comunidad" class="section alternate">
  <div class="heading">
    <div>
      <span class="tag">BLOG DE LA COMUNIDAD</span>
      <h2>Artículos y Guías Recientes</h2>
    </div>
    <div style="display:flex;gap:10px;align-items:center;">
      <a class="btn secondary" href="articulos.php">Ver todos</a>
      <?php if ($usuarioLogueado): ?>
        <a class="btn primary" href="crear_articulo.php">+ Crear artículo</a>
      <?php endif; ?>
    </div>
  </div>

  <div class="cards">
    <?php foreach ($articulosRecientes as $art): ?>
      <article class="panel" style="display:flex;flex-direction:column;justify-content:space-between;">
        <div>
          <div class="row" style="margin-bottom:10px;">
            <span class="tag"><?php echo htmlspecialchars(mb_strtoupper($art['categoria'])); ?></span>
            <span>★ <?php echo $art['promedio_cal'] ? $art['promedio_cal'] . '/5 (' . $art['total_votos'] . ')' : 'Sin votos'; ?></span>
          </div>
          <h3 style="margin-bottom:8px;"><?php echo htmlspecialchars($art['titulo']); ?></h3>
          <?php if ($art['juego_titulo']): ?>
            <small class="small" style="display:block;margin-bottom:8px;">Juego: <?php echo htmlspecialchars($art['juego_titulo']); ?></small>
          <?php endif; ?>
          <p style="border:0;padding:0;color:#aaa7c3;font-size:.9rem;"><?php echo htmlspecialchars(mb_strimwidth($art['contenido'], 0, 140, '...')); ?></p>
        </div>
        <div style="margin-top:18px;padding-top:12px;border-top:1px solid #ffffff18;">
          <div class="row">
            <small style="color:#aaa7c3;">Por <b><?php echo htmlspecialchars($art['nombre_usuario']); ?></b></small>
            <a class="link" href="articulo.php?id=<?php echo (int)$art['id']; ?>">Leer y calificar →</a>
          </div>
        </div>
      </article>
    <?php endforeach; ?>
  </div>

  <?php if (!$usuarioLogueado): ?>
    <div class="notice">
      <h3>¿Quieres participar en la comunidad?</h3>
      <p>Regístrate o inicia sesión para comentar videojuegos, publicar artículos y calificar contenido.</p>
      <a class="btn primary" href="acceso.php#registro">Crear cuenta</a>
    </div>
  <?php endif; ?>
</section>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>
