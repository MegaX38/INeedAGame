<?php
require __DIR__ . '/config/db.php';
$pageTitle = 'Crear Artículo | INeedAGame';
require __DIR__ . '/includes/header.php';

if (!$usuarioLogueado) {
    header('Location: acceso.php?estado=requiere_login#login');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo'] ?? '');
    $categoria = trim($_POST['categoria'] ?? 'Reseña');
    $juegoId = !empty($_POST['juego_id']) ? (int)$_POST['juego_id'] : null;
    $contenido = trim($_POST['contenido'] ?? '');
    $categoriasValidas = ['Reseña', 'Guía', 'Noticias', 'Opinión', 'Top / Listas'];

    if (mb_strlen($titulo) < 5 || mb_strlen($titulo) > 180 || mb_strlen($contenido) < 20 || !in_array($categoria, $categoriasValidas, true)) {
        $error = 'Verifica que el título (mín. 5 caracteres) y el contenido (mín. 20 caracteres) estén completos.';
    } else {
        $stmt = $pdo->prepare('INSERT INTO articulos (usuario_id, juego_id, titulo, categoria, contenido, estado) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([(int)$_SESSION['usuario_id'], $juegoId, $titulo, $categoria, $contenido, 'publicado']);
        $nuevoId = (int)$pdo->lastInsertId();
        header("Location: articulo.php?id={$nuevoId}&estado=publicado");
        exit;
    }
}

$juegosLista = $pdo->query('SELECT id, titulo FROM videojuegos ORDER BY titulo ASC')->fetchAll();
?>

<main class="section">
  <?php if ($error !== ''): ?>
    <div class="panel form-message" style="margin-bottom:24px;" role="alert"><?php echo htmlspecialchars($error); ?></div>
  <?php endif; ?>

  <div class="panel" style="max-width:760px;margin:auto;">
    <span class="tag">RF-0009 · CREACIÓN DE ARTÍCULOS</span>
    <h2 style="margin:10px 0 20px;">Publicar nuevo artículo</h2>

    <form class="form" action="crear_articulo.php" method="post">
      <label>Título del artículo
        <input type="text" name="titulo" placeholder="Ej: Top Juegos 2026 o Guía completa..." required>
      </label>

      <div class="two-columns" style="gap:16px;">
        <label>Categoría
          <select name="categoria" required>
            <option value="Reseña">Reseña</option>
            <option value="Guía">Guía</option>
            <option value="Noticias">Noticias</option>
            <option value="Opinión">Opinión</option>
            <option value="Top / Listas">Top / Listas</option>
          </select>
        </label>

        <label>Videojuego relacionado (opcional)
          <select name="juego_id">
            <option value="">Ninguno / General</option>
            <?php foreach ($juegosLista as $jl): ?>
              <option value="<?php echo (int)$jl['id']; ?>"><?php echo htmlspecialchars($jl['titulo']); ?></option>
            <?php endforeach; ?>
          </select>
        </label>
      </div>

      <label>Contenido del artículo
        <textarea name="contenido" rows="10" placeholder="Redacta aquí tu análisis, guía o noticia..." required></textarea>
      </label>

      <div style="display:flex;gap:12px;">
        <button class="btn primary" type="submit">Publicar artículo</button>
        <a class="btn secondary" href="articulos.php">Cancelar</a>
      </div>
    </form>
  </div>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>
