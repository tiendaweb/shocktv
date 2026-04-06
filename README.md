# ShockTV v2.0 - Plataforma de Streaming

Migración completa de HTML estático a PHP fullstack con base de datos SQLite, panel de administración y gestión dinámica de contenido.

## 🎯 Características Principales

✅ **Frontend Dinámico** - Interfaz idéntica al original con datos desde BD  
✅ **Panel Admin** - CRUD completo para películas/series  
✅ **BD SQLite** - Almacenamiento local sin dependencias externas  
✅ **Proveedores Modulares** - Sistema flexible para agregar servidores de streaming  
✅ **Búsqueda TMDB** - Integración en vivo con The Movie Database  
✅ **Español Forzado** - Todos los títulos y contenido en español (es-MX)  
✅ **Responsive** - Soporta TV, tablet y móvil  

## 📁 Estructura del Proyecto

```
shocktv/
├── index.php                 # Frontend principal (dinámico)
├── admin/                    # Panel de administración
│   ├── index.php            # Login
│   ├── dashboard.php        # Panel principal
│   ├── movies.php           # CRUD películas
│   ├── providers.php        # Gestión de proveedores
│   ├── auth.php             # Middleware de autenticación
│   └── logout.php           # Cerrar sesión
├── api/                      # Endpoints internos
│   ├── search.php           # Búsqueda en TMDB
│   ├── getMovies.php        # Obtener películas por sección
│   └── getProviders.php     # Obtener proveedores activos
├── config/                   # Configuración
│   ├── db.php               # Conexión SQLite
│   ├── api.php              # Configuración TMDB
│   └── constants.php        # Constantes globales
├── database/
│   ├── schema.sql           # Estructura BD
│   └── shocktv.db           # BD SQLite
├── init-db.php              # Script de inicialización
└── index.html               # HTML original (backup)
```

## 🚀 Instalación

### 1. Clonar repositorio
```bash
git clone <url>
cd shocktv
```

### 2. Configurar permisos (si es necesario)
```bash
chmod 755 database
chmod 644 database/shocktv.db
```

### 3. Inicializar base de datos
```bash
php init-db.php
```

## 📖 Guía de Uso

### 🔐 Acceso Admin

```
URL: http://localhost/admin/
Usuario: admin
Contraseña: admin123
```

### 📝 Agregar Películas

1. Ir a **Películas/Series** en el admin
2. Click **"Agregar desde TMDB"**
3. Buscar película/serie
4. Click **"Agregar"**
5. Aparecerá en la sección elegida

### 🎬 Secciones

- **Tendencias**: Películas más populares
- **Anime Latino**: Series anime en español
- **Series VIP**: Todas las series

Cada película se asigna a una sección al agregarla.

### 🔌 Gestionar Proveedores

1. Ir a **Proveedores** en admin
2. Ver lista de servidores activos
3. **Editar**: Cambiar nombre, URL, idioma, prioridad
4. **Agregar**: Nueva URL de embedding
5. **Eliminar**: Remover servidor

**Patrón de URL válido:**
```
https://ejemplo.com/embed/{type}?id={id}&season={season}&ep={episode}&lang={lang}
```

Variables disponibles:
- `{type}`: `movie` o `tv`
- `{id}`: TMDB ID
- `{season}`: Número de temporada (series)
- `{episode}`: Número de episodio (series)
- `{lang}`: Idioma (es-MX, es-LA, etc.)

### 🌐 Frontend

Página principal **automáticamente**:
- Carga películas de BD
- Si BD está vacía, usa TMDB en vivo
- Búsqueda en vivo contra TMDB
- Selecciona proveedor antes de reproducir

## 🗄️ Base de Datos

### Tabla `movies`
```sql
id INTEGER PRIMARY KEY
tmdb_id INTEGER (UNIQUE)
title TEXT
description TEXT
poster_path TEXT
backdrop_path TEXT
media_type TEXT (movie|tv)
section TEXT (trending|anime|series)
created_at DATETIME
updated_at DATETIME
```

### Tabla `providers`
```sql
id INTEGER PRIMARY KEY
name TEXT (UNIQUE)
embed_pattern TEXT
language_param TEXT (default: es-MX)
active BOOLEAN
priority INTEGER
created_at DATETIME
```

### Tabla `admin_users`
```sql
id INTEGER PRIMARY KEY
username TEXT (UNIQUE)
password_hash TEXT (bcrypt)
created_at DATETIME
```

### Tabla `search_cache`
```sql
id INTEGER PRIMARY KEY
query TEXT
results TEXT (JSON)
expires_at DATETIME
created_at DATETIME
```

## ⚙️ Configuración

### API TMDB
Archivo: `config/api.php`
```php
define('TMDB_API_KEY', '2628b2d65ef5a50b08d992e0a7c2de56');
define('LANGUAGE', 'es-MX'); // IMPORTANTE: SIEMPRE ESPAÑOL
```

### BD SQLite
Archivo: `config/db.php`
```php
define('DB_PATH', __DIR__ . '/../database/shocktv.db');
```

## 🔒 Seguridad

✅ Contraseñas hasheadas con bcrypt  
✅ Sesiones PHP con timeout (30 min)  
✅ Protección contra inyección SQL (PDO prepared statements)  
✅ Validación de inputs  

## 🐛 Troubleshooting

### "Database Error"
- Verifica permisos en `/database/`
- Ejecuta `php init-db.php` nuevamente

### "No results from TMDB"
- Verifica conexión a internet
- Comprueba API key en `config/api.php`

### "Login no funciona"
- Borra cookies del navegador
- Limpia sesiones PHP

### "Películas no aparecen"
- Agrega películas desde admin primero
- O actualiza `/admin/movies.php`

## 📊 Estadísticas Por Defecto

Después de `init-db.php`:
- ✅ 1 usuario admin
- ✅ 4 proveedores de streaming
- ✅ 0 películas (agregar manualmente o desde TMDB)

## 🌍 Idioma

**IMPORTANTE**: Todos los títulos y contenido se mostrarán en **ESPAÑOL (es-MX)**:

- TMDB API: parámetro `language=es-MX` forzado
- Episodios: título en español
- Búsqueda: resultados en español
- UI: íntegramente en español

## 📱 Responsive Design

- **TV**: Zoom 1.4x, botones grandes
- **Desktop**: Layout completo con sidebar
- **Tablet**: Grid adaptable
- **Móvil**: Menú flotante, grid 2 columnas

## 🔄 API Interna

### GET `/api/search.php?q=query`
Busca en TMDB, retorna JSON con resultados

### GET `/api/getMovies.php?section=trending&page=1`
Obtiene películas guardadas, soporta paginación

### GET `/api/getProviders.php`
Lista proveedores activos ordenados por prioridad

## 📝 Changelog

### v2.0 (Actual)
- Migración a PHP con BD SQLite
- Panel admin completo
- CRUD dinámico
- Gestión modular de proveedores
- API interna
- Forzar idioma español

### v1.0
- HTML estático
- TMDB en vivo
- 4 proveedores hardcodeados

## 🤝 Contribuir

Para agregar funcionalidades:
1. Crea rama desde `main`
2. Implementa feature
3. Prueba en admin
4. Haz PR

## 📄 Licencia

Private - ShockTV

---

**Creado con ❤️ por Claude Code**
