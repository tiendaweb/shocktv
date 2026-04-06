<?php
/**
 * ShockTV Frontend - PHP Dynamic Version
 * IMPORTANTE: SIEMPRE ESPAÑOL (es-MX) en todas las consultas a TMDB
 */

require_once __DIR__ . '/config/constants.php';

// Obtener películas de la base de datos para la sección por defecto
$db = getDB();
$trendingMovies = $db->query('
    SELECT * FROM movies
    WHERE section = "trending"
    ORDER BY created_at DESC
    LIMIT 20
')->fetchAll(PDO::FETCH_ASSOC);

// Si no hay películas en trending, cargar desde TMDB
if (empty($trendingMovies)) {
    $trendingData = getTrendingMovies();
    $trendingMovies = $trendingData['results'] ?? [];
}
?>
<!DOCTYPE html>
<html lang="es-MX">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ShockTV | Streaming en Español</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;600;900&display=swap');

        :root { --accent: #e11d48; }
        body { background: #020205; color: white; font-family: 'Outfit', sans-serif; overflow-x: hidden; }

        /* DETECTOR DE DISPOSITIVOS */
        .device-tv { zoom: 1.4; font-weight: 700; }
        .device-mobile { font-size: 14px; }

        /* MENÚ FLOTANTE PROFESIONAL */
        #sidebar { z-index: 10000; background: #07070a; border-right: 1px solid #1a1a20; transition: transform 0.4s ease; }
        #overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.9); z-index: 9999; backdrop-filter: blur(8px); }
        #overlay.active { display: block; }

        .nav-btn { width: 100%; display: flex; align-items: center; gap: 15px; padding: 16px; border-radius: 15px; color: #666; transition: 0.3s; }
        .nav-btn.active, .nav-btn:hover { background: var(--accent); color: white; box-shadow: 0 10px 20px rgba(225, 29, 72, 0.3); }

        /* REPRODUCTOR */
        .video-wrapper { position: relative; padding-bottom: 56.25%; background: #000; border-radius: 25px; overflow: hidden; border: 2px solid #1a1a1f; box-shadow: 0 0 40px rgba(225, 29, 72, 0.1); }
        .video-wrapper iframe { position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: none; }

        /* SERVIDORES LATINOS */
        .srv-tag { background: #111; border: 1px solid #222; padding: 12px; border-radius: 12px; cursor: pointer; text-align: center; font-size: 11px; font-weight: 800; transition: 0.2s; }
        .srv-tag:hover { border-color: var(--accent); color: var(--accent); }
        .srv-tag.active { background: var(--accent); color: white; border-color: var(--accent); }

        /* EPISODIOS */
        .ep-card { background: #0a0a0f; border: 1px solid #1a1a20; padding: 12px; border-radius: 12px; cursor: pointer; display: flex; align-items: center; gap: 12px; transition: 0.2s; }
        .ep-card:hover { border-color: var(--accent); transform: translateX(5px); }
        .ep-card img { width: 90px; height: 50px; border-radius: 8px; object-fit: cover; }

        /* ADMIN BADGE */
        .admin-badge { position: fixed; bottom-6 right-6; z-index: 9000; }
    </style>
</head>
<body class="device-pc">

    <div id="overlay" onclick="toggleMenu()"></div>

    <aside id="sidebar" class="fixed top-0 left-0 h-full w-72 p-8 -translate-x-full lg:translate-x-0">
        <div class="mb-12">
            <h1 class="text-2xl sm:text-3xl font-black text-rose-600 italic uppercase">SHOCK<span class="text-white">TV</span></h1>
            <p id="deviceTxt" class="text-xs text-gray-600 font-bold tracking-widest mt-2 uppercase"></p>
        </div>
        <nav class="space-y-2">
            <button onclick="loadSection('trending', this)" class="nav-btn active text-sm sm:text-base"><i class="fas fa-fire"></i> Tendencias</button>
            <button onclick="loadSection('anime', this)" class="nav-btn text-sm sm:text-base"><i class="fas fa-dragon"></i> Anime Latino</button>
            <button onclick="loadSection('series', this)" class="nav-btn text-sm sm:text-base"><i class="fas fa-tv"></i> Series VIP</button>
        </nav>
        <hr class="border-white/20 my-8">
        <a href="/admin/" class="block px-4 py-2 text-xs font-bold text-gray-400 hover:text-rose-600 transition">
            <i class="fas fa-lock mr-2"></i> Panel Admin
        </a>
    </aside>

    <main class="lg:ml-72 min-h-screen">
        <header class="p-3 sm:p-6 sticky top-0 bg-[#020205]/95 backdrop-blur-xl z-[500] flex items-center justify-between border-b border-white/5">
            <button onclick="toggleMenu()" class="lg:hidden text-xl sm:text-2xl text-rose-600"><i class="fas fa-bars"></i></button>
            <div class="relative flex-1 max-w-xl mx-2 sm:mx-4">
                <input id="searchInp" type="search" placeholder="Buscar..." class="w-full bg-[#0a0a0f] border border-white/10 rounded-2xl py-2 sm:py-3 px-10 sm:px-12 text-sm sm:text-base outline-none focus:ring-2 focus:ring-rose-600">
                <i class="fas fa-search absolute left-3 sm:left-4 top-2 sm:top-4 text-white/10 text-sm"></i>
            </div>
        </header>

        <section id="playerSec" class="hidden p-3 sm:p-6 lg:p-10 animate-in fade-in">
            <div class="max-w-6xl mx-auto">
                <button onclick="closePlayer()" class="mb-6 text-xs sm:text-sm font-black text-gray-500 hover:text-white uppercase"><i class="fas fa-arrow-left mr-2"></i> Volver</button>

                <div class="video-wrapper mb-6">
                    <iframe id="mainPlayer" src="" allowfullscreen allow="autoplay"></iframe>
                </div>

                <div id="providerContainer" class="mb-10 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2 sm:gap-3">
                    <!-- Providers loaded dynamically -->
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 lg:gap-12">
                    <div class="lg:col-span-2">
                        <h2 id="mTitle" class="text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-black italic uppercase text-rose-600 mb-4"></h2>
                        <p id="mDesc" class="text-gray-400 text-xs sm:text-sm leading-relaxed mb-6"></p>
                    </div>
                    <div id="epContainer" class="hidden">
                        <h3 class="text-xs font-black mb-6 uppercase text-gray-500 tracking-widest italic">Episodios Temp. 1</h3>
                        <div id="epList" class="space-y-2 max-h-[250px] sm:max-h-[400px] overflow-y-auto pr-2"></div>
                    </div>
                </div>
            </div>
        </section>

        <section id="gridSec" class="p-3 sm:p-6 lg:p-10">
            <h2 id="sectionTitle" class="text-lg sm:text-xl md:text-2xl font-black italic mb-6 sm:mb-10 border-l-4 border-rose-600 pl-4 uppercase">Recomendados</h2>
            <div id="grid" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3 sm:gap-4 md:gap-6 lg:gap-8"></div>
        </section>
    </main>

    <script>
        // IMPORTANTE: SIEMPRE ESPAÑOL (es-MX) en todas las consultas a TMDB
        const IMG = 'https://image.tmdb.org/t/p/w500';
        let currentItem = { id: null, isTV: false, ep: 1, dbId: null };
        let providers = [];

        function detectDevice() {
            const ua = navigator.userAgent.toLowerCase();
            if (ua.includes("smart-tv") || ua.includes("googletv")) document.body.className = "device-tv";
            else if (/android|iphone|ipad/.test(ua)) document.body.className = "device-mobile";
            else document.body.className = "device-pc";
            document.getElementById('deviceTxt').innerText = "ShockTV PHP v2.0";
        }

        function toggleMenu() {
            document.getElementById('sidebar').classList.toggle('-translate-x-full');
            document.getElementById('overlay').classList.toggle('active');
        }

        // Cargar proveedores al inicializar
        async function loadProviders() {
            try {
                const res = await fetch('/api/getProviders.php');
                const data = await res.json();
                providers = data.providers || [];
                console.log('Proveedores cargados:', providers);
            } catch (e) {
                console.error('Error cargando proveedores:', e);
            }
        }

        async function loadSection(type, btn) {
            if(btn) {
                document.querySelectorAll('.nav-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
            }
            if(window.innerWidth < 1024 && document.getElementById('overlay').classList.contains('active')) toggleMenu();

            // Cargar películas desde la API interna
            try {
                const res = await fetch('/api/getMovies.php?section=' + type);
                const data = await res.json();
                const movies = data.results || [];

                // Si no hay películas en BD, intentar TMDB
                if (movies.length === 0) {
                    let url = `https://api.themoviedb.org/3/trending/all/week?api_key=2628b2d65ef5a50b08d992e0a7c2de56&language=es-MX`;
                    if(type === 'anime') url = `https://api.themoviedb.org/3/discover/tv?api_key=2628b2d65ef5a50b08d992e0a7c2de56&with_genres=16&with_original_language=ja&language=es-MX`;
                    if(type === 'series') url = `https://api.themoviedb.org/3/discover/tv?api_key=2628b2d65ef5a50b08d992e0a7c2de56&sort_by=popularity.desc&language=es-MX`;

                    const tmdbRes = await fetch(url);
                    const tmdbData = await tmdbRes.json();
                    renderGrid(tmdbData.results, type !== 'trending' && type !== 'movie');
                } else {
                    renderGrid(movies, false);
                }
            } catch (e) {
                console.error('Error cargando sección:', e);
            }
        }

        function renderGrid(items, isTV = false) {
            const g = document.getElementById('grid');
            g.innerHTML = '';
            items.forEach(i => {
                const div = document.createElement('div');
                div.className = 'cursor-pointer hover:scale-105 transition duration-500';
                const poster = i.poster_path ? IMG + i.poster_path : 'https://via.placeholder.com/500x750/111/fff?text=Sin+poster';
                div.onclick = () => openPlayer(i, isTV || i.media_type === 'tv', i.id || i.tmdb_id);
                div.innerHTML = `<img src="${poster}" class="rounded-2xl mb-3 shadow-lg border border-white/5 w-full" onerror="this.src='https://via.placeholder.com/500x750/111/fff?text=Error'"><h3 class="text-[11px] font-black truncate uppercase px-1">${i.title || i.name}</h3>`;
                g.appendChild(div);
            });
        }

        async function openPlayer(item, isTV, dbId = null) {
            currentItem = { id: item.id || item.tmdb_id, isTV: isTV, ep: 1, dbId: dbId };
            document.getElementById('gridSec').classList.add('hidden');
            document.getElementById('playerSec').classList.remove('hidden');
            document.getElementById('mTitle').innerText = item.title || item.name;
            document.getElementById('mDesc').innerText = item.overview || item.description || 'Sin descripción disponible';

            // Crear botones de proveedores dinámicos
            const provContainer = document.getElementById('providerContainer');
            provContainer.innerHTML = '';
            providers.forEach((p, idx) => {
                const btn = document.createElement('div');
                btn.className = `srv-tag ${idx === 0 ? 'active' : ''}`;
                btn.textContent = p.name;
                btn.onclick = () => setStream(p.id, btn);
                provContainer.appendChild(btn);
            });

            if(isTV) {
                document.getElementById('epContainer').classList.remove('hidden');
                loadEpisodes(currentItem.id);
            } else {
                document.getElementById('epContainer').classList.add('hidden');
                if (providers.length > 0) {
                    setStream(providers[0].id);
                }
            }
            window.scrollTo({top: 0, behavior: 'smooth'});
        }

        async function loadEpisodes(id) {
            // IMPORTANTE: FORZAR ESPAÑOL (es-MX)
            try {
                const res = await fetch(`https://api.themoviedb.org/3/tv/${id}/season/1?api_key=2628b2d65ef5a50b08d992e0a7c2de56&language=es-MX`);
                const data = await res.json();
                const el = document.getElementById('epList');
                el.innerHTML = '';

                if (data.episodes) {
                    data.episodes.forEach(e => {
                        const card = document.createElement('div');
                        card.className = 'ep-card';
                        card.onclick = () => { currentItem.ep = e.episode_number; if (providers.length > 0) setStream(providers[0].id); };
                        const still = e.still_path ? IMG + e.still_path : 'https://via.placeholder.com/90x50/111/fff?text=EP+' + e.episode_number;
                        card.innerHTML = `<img src="${still}" onerror="this.src='https://via.placeholder.com/90x50/111/fff?text=EP+${e.episode_number}'"><div><p class="font-bold text-[10px] uppercase">${e.name || 'Capítulo ' + e.episode_number}</p></div>`;
                        el.appendChild(card);
                    });
                }
                if (providers.length > 0) setStream(providers[0].id);
            } catch (e) {
                console.error('Error cargando episodios:', e);
            }
        }

        // Generar URL de reproducción desde patrón del proveedor
        function generateStreamURL(provider, id, isTV, episode = 1) {
            let url = provider.embed_pattern;
            url = url.replace('{type}', isTV ? 'tv' : 'movie');
            url = url.replace('{id}', id);
            url = url.replace('{season}', '1');
            url = url.replace('{episode}', episode);
            url = url.replace('{lang}', provider.language_param || 'es-MX');
            return url;
        }

        function setStream(providerId, btn) {
            if(btn) {
                document.querySelectorAll('.srv-tag').forEach(t => t.classList.remove('active'));
                btn.classList.add('active');
            }

            const provider = providers.find(p => p.id === providerId);
            if (!provider) return;

            const { id, isTV, ep } = currentItem;
            const player = document.getElementById('mainPlayer');
            player.src = generateStreamURL(provider, id, isTV, ep);
        }

        function closePlayer() {
            document.getElementById('playerSec').classList.add('hidden');
            document.getElementById('gridSec').classList.remove('hidden');
            document.getElementById('mainPlayer').src = '';
        }

        // Búsqueda en vivo
        let searchTimeout;
        document.getElementById('searchInp').addEventListener('input', function(e) {
            clearTimeout(searchTimeout);
            const query = e.target.value.trim();

            if (query.length < 2) {
                return;
            }

            searchTimeout = setTimeout(() => {
                fetch('/api/search.php?q=' + encodeURIComponent(query))
                    .then(r => r.json())
                    .then(data => {
                        const results = data.results || [];
                        if (results.length > 0) {
                            openPlayer(results[0], results[0].media_type === 'tv');
                        }
                    })
                    .catch(e => console.error('Search error:', e));
            }, 500);
        });

        // GESTOS TÁCTILES - Swipe para abrir/cerrar menú
        let touchStartX = 0;
        let touchEndX = 0;

        document.addEventListener('touchstart', e => {
            touchStartX = e.changedTouches[0].screenX;
        }, false);

        document.addEventListener('touchend', e => {
            touchEndX = e.changedTouches[0].screenX;
            handleSwipe();
        }, false);

        function handleSwipe() {
            const threshold = 50;
            const sidebarEl = document.getElementById('sidebar');
            const overlayEl = document.getElementById('overlay');

            // Swipe izquierda: cerrar menú
            if (touchStartX - touchEndX > threshold) {
                if (overlayEl.classList.contains('active')) {
                    toggleMenu();
                }
            }

            // Swipe derecha: abrir menú (solo en móvil)
            if (touchEndX - touchStartX > threshold) {
                if (window.innerWidth < 1024 && !overlayEl.classList.contains('active')) {
                    toggleMenu();
                }
            }
        }

        // Inicializar
        detectDevice();
        loadProviders();
        loadSection('trending');
    </script>
</body>
</html>
