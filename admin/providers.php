<?php
session_start();
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config/constants.php';

requireAdmin();

$db = getDB();
$error = '';
$success = '';
$editProvider = null;

// Manejar eliminación
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $stmt = $db->prepare('DELETE FROM providers WHERE id = ?');
    if ($stmt->execute([$_GET['delete']])) {
        $success = 'Proveedor eliminado correctamente';
    }
}

// Manejar edición
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $stmt = $db->prepare('SELECT * FROM providers WHERE id = ?');
    $stmt->execute([$_GET['edit']]);
    $editProvider = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Manejar actualización o creación
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $pattern = trim($_POST['embed_pattern'] ?? '');
    $langParam = trim($_POST['language_param'] ?? 'es-MX');
    $active = isset($_POST['active']) ? 1 : 0;
    $priority = intval($_POST['priority'] ?? 0);

    if (empty($name) || empty($pattern)) {
        $error = 'Nombre y patrón de embed son requeridos';
    } else {
        if (isset($_POST['provider_id']) && is_numeric($_POST['provider_id'])) {
            // Actualizar
            $stmt = $db->prepare('
                UPDATE providers
                SET name = ?, embed_pattern = ?, language_param = ?, active = ?, priority = ?
                WHERE id = ?
            ');
            if ($stmt->execute([$name, $pattern, $langParam, $active, $priority, $_POST['provider_id']])) {
                $success = 'Proveedor actualizado correctamente';
                $editProvider = null;
            }
        } else {
            // Crear nuevo
            $stmt = $db->prepare('
                INSERT INTO providers (name, embed_pattern, language_param, active, priority)
                VALUES (?, ?, ?, ?, ?)
            ');
            if ($stmt->execute([$name, $pattern, $langParam, $active, $priority])) {
                $success = 'Proveedor agregado correctamente';
            } else {
                $error = 'El proveedor ya existe';
            }
        }
    }
}

// Obtener proveedores ordenados por prioridad
$providers = $db->query('
    SELECT * FROM providers
    ORDER BY priority ASC
')->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es-MX">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proveedores - ShockTV Admin</title>
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
                <a href="movies.php" class="block px-4 py-3 text-gray-400 hover:bg-white/5 rounded-lg font-bold text-sm transition">
                    <i class="fas fa-film mr-3"></i> Películas/Series
                </a>
                <a href="providers.php" class="block px-4 py-3 bg-rose-600 text-white rounded-lg font-bold text-sm transition">
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
                    <h2 class="text-2xl font-black italic uppercase">Proveedores de Streaming</h2>
                    <p class="text-xs text-gray-500 mt-1">Gestiona los servidores de reproducción</p>
                </div>
                <button onclick="openProviderModal()" class="px-6 py-3 bg-rose-600 hover:bg-rose-700 rounded-lg font-bold transition flex items-center gap-2">
                    <i class="fas fa-plus"></i> Nuevo Proveedor
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
                <?php if ($editProvider): ?>
                    <div class="fixed inset-0 bg-black/80 flex items-center justify-center z-50 p-4">
                        <div class="bg-[#0a0a0f] rounded-xl border border-white/10 p-8 max-w-lg w-full">
                            <h3 class="text-2xl font-black mb-6">Editar Proveedor</h3>
                            <form method="POST" class="space-y-4">
                                <input type="hidden" name="provider_id" value="<?php echo $editProvider['id']; ?>">

                                <div>
                                    <label class="block text-xs font-bold text-gray-400 mb-2">Nombre</label>
                                    <input type="text" name="name" value="<?php echo htmlspecialchars($editProvider['name']); ?>"
                                           class="w-full bg-[#111] border border-white/10 rounded px-3 py-2 outline-none focus:ring-2 focus:ring-rose-600" required>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-gray-400 mb-2">Patrón Embed</label>
                                    <textarea name="embed_pattern" rows="3"
                                              class="w-full bg-[#111] border border-white/10 rounded px-3 py-2 outline-none focus:ring-2 focus:ring-rose-600 font-mono text-xs"><?php echo htmlspecialchars($editProvider['embed_pattern']); ?></textarea>
                                    <p class="text-xs text-gray-500 mt-2">Usa: {type}, {id}, {season}, {episode}, {lang}</p>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-gray-400 mb-2">Parámetro Idioma</label>
                                    <input type="text" name="language_param" value="<?php echo htmlspecialchars($editProvider['language_param']); ?>"
                                           class="w-full bg-[#111] border border-white/10 rounded px-3 py-2 outline-none focus:ring-2 focus:ring-rose-600"
                                           placeholder="es-MX">
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-gray-400 mb-2">Prioridad (menor = más alto)</label>
                                    <input type="number" name="priority" value="<?php echo $editProvider['priority']; ?>"
                                           class="w-full bg-[#111] border border-white/10 rounded px-3 py-2 outline-none focus:ring-2 focus:ring-rose-600">
                                </div>

                                <div class="flex items-center gap-2">
                                    <input type="checkbox" name="active" id="active" <?php echo $editProvider['active'] ? 'checked' : ''; ?>>
                                    <label for="active" class="text-sm">Activo</label>
                                </div>

                                <div class="flex gap-3 pt-4">
                                    <button type="submit" class="flex-1 bg-rose-600 hover:bg-rose-700 py-2 rounded font-bold transition">
                                        Guardar Cambios
                                    </button>
                                    <a href="providers.php" class="flex-1 bg-gray-700 hover:bg-gray-600 py-2 rounded font-bold transition text-center">
                                        Cancelar
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Providers List -->
                <?php if (!empty($providers)): ?>
                    <div class="grid gap-4">
                        <?php foreach ($providers as $provider): ?>
                            <div class="bg-[#0a0a0f] border border-white/10 p-6 rounded-lg hover:border-rose-600 transition group">
                                <div class="flex items-start justify-between mb-4">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-3 mb-2">
                                            <h3 class="font-bold text-lg"><?php echo htmlspecialchars($provider['name']); ?></h3>
                                            <span class="text-xs px-2 py-1 bg-<?php echo $provider['active'] ? 'green-900/50 border-green-600 text-green-300' : 'gray-700 text-gray-400'; ?> border rounded">
                                                <?php echo $provider['active'] ? 'Activo' : 'Inactivo'; ?>
                                            </span>
                                        </div>
                                        <p class="text-xs text-gray-500 font-mono break-all"><?php echo htmlspecialchars($provider['embed_pattern']); ?></p>
                                        <p class="text-xs text-gray-600 mt-2">
                                            <i class="fas fa-language mr-1"></i> Idioma: <?php echo htmlspecialchars($provider['language_param']); ?> •
                                            <i class="fas fa-layer-group mr-1"></i> Prioridad: <?php echo $provider['priority']; ?>
                                        </p>
                                    </div>
                                </div>
                                <div class="flex gap-2">
                                    <a href="?edit=<?php echo $provider['id']; ?>" class="px-3 py-2 bg-blue-600/20 hover:bg-blue-600/40 border border-blue-600/50 rounded text-sm font-bold transition">
                                        <i class="fas fa-edit mr-1"></i> Editar
                                    </a>
                                    <a href="?delete=<?php echo $provider['id']; ?>" onclick="return confirm('¿Eliminar este proveedor?')"
                                       class="px-3 py-2 bg-red-600/20 hover:bg-red-600/40 border border-red-600/50 rounded text-sm font-bold transition">
                                        <i class="fas fa-trash mr-1"></i> Eliminar
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="bg-[#0a0a0f] border border-white/10 rounded-lg p-12 text-center">
                        <i class="fas fa-server text-4xl text-gray-600 mb-4"></i>
                        <p class="text-gray-400">Sin proveedores configurados</p>
                    </div>
                <?php endif; ?>
            </section>
        </main>
    </div>

    <!-- Provider Modal -->
    <div id="providerModal" class="hidden fixed inset-0 bg-black/80 flex items-center justify-center z-50 p-4">
        <div class="bg-[#0a0a0f] rounded-xl border border-white/10 p-8 max-w-lg w-full">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-2xl font-black">Nuevo Proveedor</h3>
                <button onclick="closeProviderModal()" class="text-2xl text-gray-500 hover:text-white">×</button>
            </div>

            <form method="POST" class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-gray-400 mb-2">Nombre</label>
                    <input type="text" name="name" placeholder="Ej: Latino Premium"
                           class="w-full bg-[#111] border border-white/10 rounded px-3 py-2 outline-none focus:ring-2 focus:ring-rose-600" required>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-400 mb-2">Patrón Embed</label>
                    <textarea name="embed_pattern" rows="3" placeholder="https://servidor.com/embed/{type}?id={id}&lang={lang}"
                              class="w-full bg-[#111] border border-white/10 rounded px-3 py-2 outline-none focus:ring-2 focus:ring-rose-600 font-mono text-xs" required></textarea>
                    <p class="text-xs text-gray-500 mt-2">Usa: {type} (movie/tv), {id}, {season}, {episode}, {lang}</p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-400 mb-2">Parámetro Idioma</label>
                    <input type="text" name="language_param" value="es-MX"
                           class="w-full bg-[#111] border border-white/10 rounded px-3 py-2 outline-none focus:ring-2 focus:ring-rose-600">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-400 mb-2">Prioridad (menor = más alto)</label>
                    <input type="number" name="priority" value="99"
                           class="w-full bg-[#111] border border-white/10 rounded px-3 py-2 outline-none focus:ring-2 focus:ring-rose-600">
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" name="active" id="activeNew" checked>
                    <label for="activeNew" class="text-sm">Activo</label>
                </div>

                <div class="flex gap-3 pt-4">
                    <button type="submit" class="flex-1 bg-rose-600 hover:bg-rose-700 py-2 rounded font-bold transition">
                        Agregar Proveedor
                    </button>
                    <button type="button" onclick="closeProviderModal()" class="flex-1 bg-gray-700 hover:bg-gray-600 py-2 rounded font-bold transition">
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openProviderModal() {
            document.getElementById('providerModal').classList.remove('hidden');
        }

        function closeProviderModal() {
            document.getElementById('providerModal').classList.add('hidden');
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeProviderModal();
        });
    </script>
</body>
</html>
