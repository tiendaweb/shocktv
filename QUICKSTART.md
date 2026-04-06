# ShockTV v2.0 - Quick Start Guide

## ⚡ 30 segundos para empezar

```bash
# 1. Inicializar BD
php init-db.php

# 2. Abrir en el navegador
http://localhost/admin/

# 3. Ingresar
Usuario: admin
Contraseña: admin123
```

## 🎬 Agregar tu primera película

1. **Login admin** → http://localhost/admin/
2. Click en **"Películas/Series"**
3. Click **"Agregar desde TMDB"**
4. Buscar: `The Matrix`, `Breaking Bad`, etc.
5. Click **"Agregar"**
6. ¡Listo! Aparecerá en http://localhost/

## 🔧 Configuración Básica

### Cambiar contraseña admin
```bash
php install.php  # Ejecuta el instalador interactivo
```

### Editar película
1. Panel Admin → Películas/Series
2. Hover sobre película
3. Click en lápiz (edit)
4. Cambiar título, descripción, sección
5. Guardar

### Agregar proveedor de streaming
1. Panel Admin → Proveedores
2. Click **"Nuevo Proveedor"**
3. Ingresar:
   - Nombre: `Mi servidor`
   - Patrón: `https://servidor.com/embed/{type}?id={id}&lang={lang}`
   - Idioma: `es-MX`
4. Guardar

**Variables disponibles en patrón:**
- `{type}` = `movie` o `tv`
- `{id}` = TMDB ID
- `{season}` = temporada
- `{episode}` = episodio
- `{lang}` = idioma

## 📊 Carpetas importantes

```
config/          ← Configuración de BD y APIs
admin/           ← Panel administrativo
api/             ← Endpoints internos
database/        ← BD SQLite (shocktv.db)
```

## 🚀 En Producción

### Apache
```bash
# Copiar .htaccess ya existe, solo:
a2enmod rewrite
systemctl restart apache2
```

### Nginx
```bash
# Copiar configuración
cp nginx.conf.example /etc/nginx/sites-available/shocktv
# Editar server_name y root path
# Recargar Nginx
systemctl restart nginx
```

## 🔐 Seguridad

✅ Cambiar contraseña admin  
✅ Proteger `/config` y `/database` en servidor  
✅ Usar HTTPS en producción  
✅ Actualizar TMDB API key si es propia  

## 🐛 Problemas Comunes

**"Database Error"**
```bash
chmod 755 database
php init-db.php
```

**"No aparecen películas"**
- Agrega desde admin primero
- O ejecuta: `php init-db.php`

**"Login no funciona"**
- Limpiar cookies del navegador
- Limpiar sesiones PHP

## 📚 Documentación Completa

Ver: [README.md](README.md)

## 🎯 Roadmap

- [ ] Cambiar contraseña admin
- [ ] Agregar 5+ películas
- [ ] Probar proveedores
- [ ] Configurar servidor web
- [ ] Hacer backup de BD

---

**¿Necesitas ayuda?** Ver README.md sección Troubleshooting
