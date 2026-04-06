<?php
session_start();

require_once '../config/constants.php';

// Si ya está logueado, redirigir al dashboard
if (isset($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Usuario y contraseña son requeridos';
    } else {
        $db = getDB();
        $stmt = $db->prepare('SELECT id, password_hash FROM admin_users WHERE username = ?');
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_username'] = $username;
            $_SESSION['login_time'] = time();
            header('Location: /admin/dashboard');
            exit;
        } else {
            $error = 'Usuario o contraseña incorrectos';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es-MX">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - ShockTV</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;600;900&display=swap');
        body {
            background: #020205;
            color: white;
            font-family: 'Outfit', sans-serif;
        }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen">
    <div class="w-full max-w-md px-6">
        <div class="text-center mb-12">
            <h1 class="text-4xl font-black text-rose-600 italic uppercase mb-2">SHOCK<span class="text-white">TV</span></h1>
            <p class="text-gray-400 text-sm">Panel de Administración</p>
        </div>

        <?php if ($error): ?>
            <div class="bg-red-900/30 border border-red-500 text-red-200 px-4 py-3 rounded-lg mb-6 text-sm">
                <i class="fas fa-exclamation-circle mr-2"></i><?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="bg-green-900/30 border border-green-500 text-green-200 px-4 py-3 rounded-lg mb-6 text-sm">
                <i class="fas fa-check-circle mr-2"></i><?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-6 bg-[#0a0a0f] p-8 rounded-2xl border border-white/10">
            <div>
                <label class="block text-xs font-bold text-gray-400 mb-2 uppercase tracking-widest">Usuario</label>
                <input
                    type="text"
                    name="username"
                    class="w-full bg-[#111] border border-white/10 rounded-lg py-3 px-4 text-white outline-none focus:ring-2 focus:ring-rose-600 focus:border-transparent"
                    placeholder="admin"
                    required
                >
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-400 mb-2 uppercase tracking-widest">Contraseña</label>
                <input
                    type="password"
                    name="password"
                    class="w-full bg-[#111] border border-white/10 rounded-lg py-3 px-4 text-white outline-none focus:ring-2 focus:ring-rose-600 focus:border-transparent"
                    placeholder="••••••••"
                    required
                >
            </div>

            <button
                type="submit"
                class="w-full bg-rose-600 hover:bg-rose-700 text-white font-bold py-3 rounded-lg transition duration-300 uppercase text-sm"
            >
                <i class="fas fa-sign-in-alt mr-2"></i> Ingresar
            </button>
        </form>

        <p class="text-center text-gray-500 text-xs mt-8">
            Por defecto: admin@shocktv.com / admin@shocktv.com
        </p>
    </div>
</body>
</html>
