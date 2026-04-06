<?php
session_start();
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config/constants.php';

requireAdmin();

$db = getDB();

// Obtener estadísticas
$statsMovies = $db->query('SELECT COUNT(*) as count FROM movies WHERE media_type = "movie"')->fetch(PDO::FETCH_ASSOC);
$statsTV = $db->query('SELECT COUNT(*) as count FROM movies WHERE media_type = "tv"')->fetch(PDO::FETCH_ASSOC);
$statsProviders = $db->query('SELECT COUNT(*) as count FROM providers WHERE active = 1')->fetch(PDO::FETCH_ASSOC);

// Obtener películas recientes
$recentMovies = $db->query('
    SELECT * FROM movies
    ORDER BY created_at DESC
    LIMIT 5
')->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es-MX">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - ShockTV</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;600;900&display=swap');
        :root { --accent: #e11d48; }
        body { background: #020205; color: white; font-family: 'Outfit', sans-serif; }
        .stat-card { background: #0a0a0f; border: 1px solid #1a1a20; border-radius: 12px; padding: 20px; }
        .stat-card.accent { border-color: var(--accent); }
    </style>
</head>
<body>
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <aside class="w-72 bg-[#07070a] border-r border-white/10 p-8 sticky top-0 h-screen overflow-y-auto">
            <div class="mb-12">
                <h1 class="text-2xl font-black text-rose-600 italic uppercase">SHOCK<span class="text-white">TV</span></h1>
                <p class="text-xs text-gray-600 font-bold mt-2">Admin Panel v2.0</p>
            </div>

            <nav class="space-y-3">
                <a href="/admin/dashboard" class="block px-4 py-3 bg-rose-600 text-white rounded-lg font-bold text-sm hover:bg-rose-700 transition">
                    <i class="fas fa-chart-line mr-3"></i> Dashboard
                </a>
                <a href="/admin/movies" class="block px-4 py-3 text-gray-400 hover:bg-white/5 rounded-lg font-bold text-sm transition">
                    <i class="fas fa-film mr-3"></i> Películas/Series
                </a>
                <a href="/admin/providers" class="block px-4 py-3 text-gray-400 hover:bg-white/5 rounded-lg font-bold text-sm transition">
                    <i class="fas fa-server mr-3"></i> Proveedores
                </a>
            </nav>

            <hr class="border-white/10 my-8">

            <div class="text-xs text-gray-500 mb-6">
                <p class="font-bold mb-2">Admin logueado:</p>
                <p class="bg-white/5 px-3 py-2 rounded"><?php echo htmlspecialchars(getAdminUsername()); ?></p>
            </div>

            <a href="/admin/logout" class="w-full px-4 py-2 border border-white/20 text-gray-400 hover:text-white rounded-lg text-sm font-bold transition text-center">
                <i class="fas fa-sign-out-alt mr-2"></i> Cerrar Sesión
            </a>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 overflow-auto">
            <header class="bg-[#0a0a0f]/50 backdrop-blur border-b border-white/10 px-8 py-6 sticky top-0 z-10">
                <h2 class="text-2xl font-black italic uppercase">Dashboard</h2>
                <p class="text-xs text-gray-500 mt-1">Panel de control de ShockTV</p>
            </header>

            <section class="p-8">
                <!-- Estadísticas -->
                <div class="grid grid-cols-3 gap-6 mb-12">
                    <div class="stat-card accent">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-400 text-sm uppercase font-bold">Películas</p>
                                <p class="text-3xl font-black mt-2"><?php echo $statsMovies['count']; ?></p>
                            </div>
                            <i class="fas fa-film text-rose-600 text-3xl opacity-30"></i>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-400 text-sm uppercase font-bold">Series</p>
                                <p class="text-3xl font-black mt-2"><?php echo $statsTV['count']; ?></p>
                            </div>
                            <i class="fas fa-tv text-blue-400 text-3xl opacity-30"></i>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-400 text-sm uppercase font-bold">Proveedores Activos</p>
                                <p class="text-3xl font-black mt-2"><?php echo $statsProviders['count']; ?></p>
                            </div>
                            <i class="fas fa-server text-green-400 text-3xl opacity-30"></i>
                        </div>
                    </div>
                </div>

                <!-- Películas Recientes -->
                <div class="mb-12">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-black italic uppercase">Agregados Recientemente</h3>
                        <a href="movies.php" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 rounded-lg text-sm font-bold transition">
                            Ver todos <i class="fas fa-arrow-right ml-2"></i>
                        </a>
                    </div>

                    <?php if (!empty($recentMovies)): ?>
                        <div class="space-y-3">
                            <?php foreach ($recentMovies as $movie): ?>
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
                                            <?php echo ucfirst($movie['media_type']); ?> • TMDB ID: <?php echo $movie['tmdb_id']; ?>
                                        </p>
                                    </div>
                                    <div class="flex gap-2 opacity-0 group-hover:opacity-100 transition">
                                        <a href="movies.php?edit=<?php echo $movie['id']; ?>" class="p-2 hover:bg-rose-600 rounded transition text-xs">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="bg-[#0a0a0f] border border-white/10 rounded-lg p-8 text-center">
                            <i class="fas fa-inbox text-4xl text-gray-600 mb-4"></i>
                            <p class="text-gray-400">Sin películas agregadas aún</p>
                            <a href="movies.php" class="inline-block mt-4 px-4 py-2 bg-rose-600 hover:bg-rose-700 rounded-lg text-sm font-bold transition">
                                Agregar película
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        </main>
    </div>
</body>
</html>
