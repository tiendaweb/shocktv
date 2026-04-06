<?php
session_start();
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config/constants.php';

requireAdmin();

$db = getDB();
$error = '';
$success = '';
$editMovie = null;

// Manejar eliminación
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $stmt = $db->prepare('DELETE FROM movies WHERE id = ?');
    if ($stmt->execute([$_GET['delete']])) {
        $success = 'Película eliminada correctamente';
    }
}

// Manejar edición
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $stmt = $db->prepare('SELECT * FROM movies WHERE id = ?');
    $stmt->execute([$_GET['edit']]);
    $editMovie = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Manejar actualización
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update' && isset($_POST['movie_id'])) {
        $stmt = $db->prepare('
            UPDATE movies
            SET title = ?, description = ?, section = ?
            WHERE id = ?
        ');
        if ($stmt->execute([
            trim($_POST['title']),
            trim($_POST['description']),
            $_POST['section'],
            $_POST['movie_id']
        ])) {
            $success = 'Película actualizada correctamente';
            $editMovie = null;
        }
    } elseif ($_POST['action'] === 'import') {
        // Importar película desde búsqueda
        $tmdbId = intval($_POST['tmdb_id']);
        $title = trim($_POST['title']);
        $description = trim($_POST['description']);
        $posterPath = $_POST['poster_path'] ?? null;
        $mediaType = in_array($_POST['media_type'], ['movie', 'tv']) ? $_POST['media_type'] : 'movie';
        $section = $_POST['section'] ?? 'trending';

        $stmt = $db->prepare('
            INSERT OR IGNORE INTO movies (tmdb_id, title, description, poster_path, media_type, section)
            VALUES (?, ?, ?, ?, ?, ?)
        ');

        if ($stmt->execute([$tmdbId, $title, $description, $posterPath, $mediaType, $section])) {
            $success = 'Película agregada correctamente a la base de datos';
        } else {
            $error = 'La película ya existe en la base de datos';
        }
    }
}

// Obtener películas con paginación
$page = max(1, intval($_GET['page'] ?? 1));
$offset = ($page - 1) * ITEMS_PER_PAGE;

$total = $db->query('SELECT COUNT(*) FROM movies')->fetchColumn();
$totalPages = ceil($total / ITEMS_PER_PAGE);

$movies = $db->query("
    SELECT * FROM movies
    ORDER BY created_at DESC
    LIMIT ? OFFSET ?
")->fetchAll(PDO::FETCH_ASSOC);

// Bind parameters manually for limit/offset
$stmt = $db->prepare('
    SELECT * FROM movies
    ORDER BY created_at DESC
    LIMIT :limit OFFSET :offset
');
$stmt->bindValue(':limit', ITEMS_PER_PAGE, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$movies = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es-MX">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Películas - ShockTV Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;600;900&display=swap');
        body { background: #020205; color: white; font-family: 'Outfit', sans-serif; }
    </style>
</head>
<body>
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <aside class="w-72 bg-[#07070a] border-r border-white/10 p-8 sticky top-0 h-screen overflow-y-auto">
            <div class="mb-12">
                <h1 class="text-2xl font-black text-rose-600 italic uppercase">SHOCK<span class="text-white">TV</span></h1>
            </div>
            <nav class="space-y-3">
                <a href="dashboard.php" class="block px-4 py-3 text-gray-400 hover:bg-white/5 rounded-lg font-bold text-sm transition">
                    <i class="fas fa-chart-line mr-3"></i> Dashboard
                </a>
                <a href="movies.php" class="block px-4 py-3 bg-rose-600 text-white rounded-lg font-bold text-sm transition">
                    <i class="fas fa-film mr-3"></i> Películas/Series
                </a>
                <a href="providers.php" class="block px-4 py-3 text-gray-400 hover:bg-white/5 rounded-lg font-bold text-sm transition">
                    <i class="fas fa-server mr-3"></i> Proveedores
                </a>
            </nav>
            <hr class="border-white/10 my-8">
            <a href="logout.php" class="w-full px-4 py-2 border border-white/20 text-gray-400 hover:text-white rounded-lg text-sm font-bold transition text-center block">
                <i class="fas fa-sign-out-alt mr-2"></i> Cerrar Sesión
            </a>
        </aside>

        <!-- Main -->
        <main class="flex-1 overflow-auto">
            <header class="bg-[#0a0a0f]/50 backdrop-blur border-b border-white/10 px-8 py-6 sticky top-0 z-10 flex justify-between items-center">
                <div>
                    <h2 class="text-2xl font-black italic uppercase">Películas/Series</h2>
                    <p class="text-xs text-gray-500 mt-1">Gestiona tu catálogo</p>
                </div>
                <button onclick="openSearchModal()" class="px-6 py-3 bg-rose-600 hover:bg-rose-700 rounded-lg font-bold transition flex items-center gap-2">
                    <i class="fas fa-plus"></i> Agregar desde TMDB
                </button>
            </header>

            <section class="p-8">
                <?php if ($error): ?>
                    <div class="bg-red-900/30 border border-red-500 text-red-200 px-4 py-3 rounded-lg mb-6">
                        <i class="fas fa-exclamation-circle mr-2"></i><?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="bg-green-900/30 border border-green-500 text-green-200 px-4 py-3 rounded-lg mb-6">
                        <i class="fas fa-check-circle mr-2"></i><?php echo htmlspecialchars($success); ?>
                    </div>
                <?php endif; ?>

                <!-- Edit Modal -->
                <?php if ($editMovie): ?>
                    <div class="fixed inset-0 bg-black/80 flex items-center justify-center z-50 p-4">
                        <div class="bg-[#0a0a0f] rounded-xl border border-white/10 p-8 max-w-lg w-full">
                            <h3 class="text-2xl font-black mb-6">Editar Película</h3>
                            <form method="POST" class="space-y-4">
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" name="movie_id" value="<?php echo $editMovie['id']; ?>">

                                <div>
                                    <label class="block text-xs font-bold text-gray-400 mb-2">Título</label>
                                    <input type="text" name="title" value="<?php echo htmlspecialchars($editMovie['title']); ?>"
                                           class="w-full bg-[#111] border border-white/10 rounded px-3 py-2 outline-none focus:ring-2 focus:ring-rose-600" required>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-gray-400 mb-2">Descripción</label>
                                    <textarea name="description" rows="4"
                                              class="w-full bg-[#111] border border-white/10 rounded px-3 py-2 outline-none focus:ring-2 focus:ring-rose-600"><?php echo htmlspecialchars($editMovie['description']); ?></textarea>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-gray-400 mb-2">Sección</label>
                                    <select name="section" class="w-full bg-[#111] border border-white/10 rounded px-3 py-2 outline-none focus:ring-2 focus:ring-rose-600">
                                        <option value="trending" <?php echo $editMovie['section'] === 'trending' ? 'selected' : ''; ?>>Tendencias</option>
                                        <option value="anime" <?php echo $editMovie['section'] === 'anime' ? 'selected' : ''; ?>>Anime Latino</option>
                                        <option value="series" <?php echo $editMovie['section'] === 'series' ? 'selected' : ''; ?>>Series VIP</option>
                                    </select>
                                </div>

                                <div class="flex gap-3 pt-4">
                                    <button type="submit" class="flex-1 bg-rose-600 hover:bg-rose-700 py-2 rounded font-bold transition">
                                        Guardar Cambios
                                    </button>
                                    <a href="movies.php" class="flex-1 bg-gray-700 hover:bg-gray-600 py-2 rounded font-bold transition text-center">
                                        Cancelar
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Movies List -->
                <?php if (!empty($movies)): ?>
                    <div class="space-y-3">
                        <?php foreach ($movies as $movie): ?>
                            <div class="flex items-center gap-4 bg-[#0a0a0f] border border-white/10 p-4 rounded-lg hover:border-rose-600 transition group">
                                <?php if ($movie['poster_path']): ?>
                                    <img src="<?php echo TMDB_IMAGE_BASE . htmlspecialchars($movie['poster_path']); ?>"
                                         alt="<?php echo htmlspecialchars($movie['title']); ?>"
                                         class="w-12 h-16 rounded object-cover">
                                <?php else: ?>
                                    <div class="w-12 h-16 bg-gray-700 rounded flex items-center justify-center">
                                        <i class="fas fa-image text-gray-600"></i>
                                    </div>
                                <?php endif; ?>
                                <div class="flex-1 min-w-0">
                                    <p class="font-bold truncate"><?php echo htmlspecialchars($movie['title']); ?></p>
                                    <p class="text-xs text-gray-500">
                                        <i class="fas fa-<?php echo $movie['media_type'] === 'tv' ? 'tv' : 'film'; ?> mr-1"></i>
                                        <?php echo ucfirst($movie['media_type']); ?> • TMDB: <?php echo $movie['tmdb_id']; ?> • Sección: <?php echo ucfirst($movie['section']); ?>
                                    </p>
                                </div>
                                <div class="flex gap-2 opacity-0 group-hover:opacity-100 transition">
                                    <a href="?edit=<?php echo $movie['id']; ?>" class="p-2 hover:bg-blue-600 rounded transition text-sm">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="?delete=<?php echo $movie['id']; ?>" onclick="return confirm('¿Eliminar esta película?')"
                                       class="p-2 hover:bg-red-600 rounded transition text-sm">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Pagination -->
                    <div class="flex justify-center gap-2 mt-8">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?php echo $page - 1; ?>" class="px-3 py-2 bg-white/10 hover:bg-white/20 rounded text-sm">← Anterior</a>
                        <?php endif; ?>
                        <span class="px-3 py-2 text-sm text-gray-400">Página <?php echo $page; ?> de <?php echo max(1, $totalPages); ?></span>
                        <?php if ($page < $totalPages): ?>
                            <a href="?page=<?php echo $page + 1; ?>" class="px-3 py-2 bg-white/10 hover:bg-white/20 rounded text-sm">Siguiente →</a>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="bg-[#0a0a0f] border border-white/10 rounded-lg p-12 text-center">
                        <i class="fas fa-inbox text-4xl text-gray-600 mb-4"></i>
                        <p class="text-gray-400 mb-4">Sin películas agregadas</p>
                        <button onclick="openSearchModal()" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 rounded-lg font-bold transition">
                            Agregar película
                        </button>
                    </div>
                <?php endif; ?>
            </section>
        </main>
    </div>

    <!-- Search Modal -->
    <div id="searchModal" class="hidden fixed inset-0 bg-black/80 flex items-center justify-center z-50 p-4">
        <div class="bg-[#0a0a0f] rounded-xl border border-white/10 p-8 max-w-2xl w-full max-h-96 overflow-y-auto">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-2xl font-black">Buscar en TMDB</h3>
                <button onclick="closeSearchModal()" class="text-2xl text-gray-500 hover:text-white">×</button>
            </div>

            <div class="mb-6">
                <input type="text" id="searchInput" placeholder="Buscar película o serie en TMDB..."
                       class="w-full bg-[#111] border border-white/10 rounded px-4 py-3 outline-none focus:ring-2 focus:ring-rose-600">
            </div>

            <div id="searchResults" class="space-y-3 max-h-96 overflow-y-auto"></div>
        </div>
    </div>

    <script>
        function openSearchModal() {
            document.getElementById('searchModal').classList.remove('hidden');
            document.getElementById('searchInput').focus();
        }

        function closeSearchModal() {
            document.getElementById('searchModal').classList.add('hidden');
        }

        let searchTimeout;
        document.getElementById('searchInput').addEventListener('input', function(e) {
            clearTimeout(searchTimeout);
            const query = e.target.value.trim();

            if (query.length < 2) {
                document.getElementById('searchResults').innerHTML = '';
                return;
            }

            searchTimeout = setTimeout(() => {
                fetch('/api/search.php?q=' + encodeURIComponent(query))
                    .then(r => r.json())
                    .then(data => {
                        const results = data.results || [];
                        let html = '';

                        results.slice(0, 10).forEach(item => {
                            const poster = item.poster_path
                                ? 'https://image.tmdb.org/t/p/w92' + item.poster_path
                                : 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="92" height="138"%3E%3Crect fill="%23333" width="92" height="138"/%3E%3C/svg%3E';

                            html += `
                                <div class="flex gap-4 p-3 bg-[#111] rounded border border-white/10 hover:border-rose-600 transition">
                                    <img src="${poster}" alt="poster" class="w-12 h-16 rounded object-cover">
                                    <div class="flex-1 min-w-0">
                                        <p class="font-bold text-sm truncate">${item.title || item.name}</p>
                                        <p class="text-xs text-gray-500 mb-2">${item.media_type === 'tv' ? 'Serie' : 'Película'}</p>
                                        <button type="button" onclick="importMovie(${item.id}, '${(item.title || item.name).replace(/'/g, "\\'")}', '${item.media_type}', '${item.poster_path || ''}')"
                                                class="px-3 py-1 bg-rose-600 hover:bg-rose-700 rounded text-xs font-bold transition">
                                            Agregar
                                        </button>
                                    </div>
                                </div>
                            `;
                        });

                        document.getElementById('searchResults').innerHTML = html || '<p class="text-gray-500 text-center py-4">Sin resultados</p>';
                    });
            }, 500);
        });

        function importMovie(tmdbId, title, mediaType, posterPath) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="action" value="import">
                <input type="hidden" name="tmdb_id" value="${tmdbId}">
                <input type="hidden" name="title" value="${title}">
                <input type="hidden" name="media_type" value="${mediaType}">
                <input type="hidden" name="poster_path" value="${posterPath}">
                <input type="hidden" name="section" value="trending">
                <input type="hidden" name="description" value="">
            `;
            document.body.appendChild(form);
            form.submit();
            form.remove();
        }

        // Close modal on Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeSearchModal();
        });
    </script>
</body>
</html>
