# ShockTV v2.0 - Deployment Guide

## 🚀 Deploying to Production

### Prerequisites
- PHP 7.4 o superior
- SQLite (incluido en PHP)
- cURL (incluido en PHP)
- Apache con mod_rewrite O Nginx
- Certificado SSL (recomendado)

### Step 1: Clone y Configurar

```bash
# Clonar repositorio
git clone https://github.com/tiendaweb/shocktv.git
cd shocktv

# Crear ambiente de producción
git checkout main (o rama deseada)

# Configurar permisos
chmod 755 database
chmod 644 database/shocktv.db
chmod 755 .

# Instalar dependencias (no hay, solo PHP puro)
# Pero ejecutar inicializador
php install.php
```

### Step 2: Configurar Servidor Web

#### Apache

```bash
# Habilitar mod_rewrite
sudo a2enmod rewrite

# Crear virtual host
sudo nano /etc/apache2/sites-available/shocktv.conf
```

**Contenido:**
```apache
<VirtualHost *:80>
    ServerName shocktv.example.com
    ServerAdmin admin@example.com
    
    DocumentRoot /var/www/shocktv
    
    <Directory /var/www/shocktv>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/shocktv_error.log
    CustomLog ${APACHE_LOG_DIR}/shocktv_access.log combined
</VirtualHost>
```

**Activar:**
```bash
sudo a2ensite shocktv
sudo systemctl reload apache2
```

#### Nginx

```bash
# Crear servidor block
sudo nano /etc/nginx/sites-available/shocktv
```

**Contenido:**
```nginx
server {
    listen 80;
    server_name shocktv.example.com;
    root /var/www/shocktv;
    
    access_log /var/log/nginx/shocktv_access.log;
    error_log /var/log/nginx/shocktv_error.log;
    
    index index.php;
    
    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.1-fpm.sock;
    }
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    # Proteger archivos sensibles
    location ~ ^/(config|database|init-db\.php|install\.php) {
        deny all;
    }
}
```

**Activar:**
```bash
sudo ln -s /etc/nginx/sites-available/shocktv /etc/nginx/sites-enabled/
sudo systemctl reload nginx
```

### Step 3: SSL/HTTPS (IMPORTANTE)

```bash
# Instalar Let's Encrypt (gratuito)
sudo apt install certbot python3-certbot-apache  # Apache
# O
sudo apt install certbot python3-certbot-nginx  # Nginx

# Generar certificado
sudo certbot certonly --standalone -d shocktv.example.com

# Renovación automática (incluida en certbot)
sudo certbot renew --dry-run
```

**Apache con SSL:**
```apache
<VirtualHost *:443>
    ServerName shocktv.example.com
    DocumentRoot /var/www/shocktv
    
    SSLEngine on
    SSLCertificateFile /etc/letsencrypt/live/shocktv.example.com/fullchain.pem
    SSLCertificateKeyFile /etc/letsencrypt/live/shocktv.example.com/privkey.pem
    
    # ... resto de configuración
</VirtualHost>

# Redirigir HTTP a HTTPS
<VirtualHost *:80>
    ServerName shocktv.example.com
    Redirect permanent / https://shocktv.example.com/
</VirtualHost>
```

**Nginx con SSL:**
```nginx
server {
    listen 443 ssl http2;
    server_name shocktv.example.com;
    
    ssl_certificate /etc/letsencrypt/live/shocktv.example.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/shocktv.example.com/privkey.pem;
    
    # ... resto de configuración
}

# Redirigir HTTP a HTTPS
server {
    listen 80;
    server_name shocktv.example.com;
    return 301 https://$server_name$request_uri;
}
```

### Step 4: Seguridad

```bash
# Cambiar contraseña admin INMEDIATAMENTE
php install.php

# Restringir permisos
chmod 750 config
chmod 750 database
chmod 600 database/shocktv.db
chmod 600 .htaccess

# Crear usuario PHP dedicado
sudo useradd -r -s /bin/false shocktv
sudo chown -R shocktv:shocktv /var/www/shocktv
```

### Step 5: Backup Automático

```bash
# Crear script de backup
sudo nano /usr/local/bin/backup-shocktv.sh
```

**Contenido:**
```bash
#!/bin/bash
BACKUP_DIR="/backups/shocktv"
DB_FILE="/var/www/shocktv/database/shocktv.db"
DATE=$(date +%Y%m%d_%H%M%S)

mkdir -p $BACKUP_DIR
cp $DB_FILE "$BACKUP_DIR/shocktv_$DATE.db"

# Mantener solo últimos 30 días
find $BACKUP_DIR -name "shocktv_*.db" -mtime +30 -delete

echo "Backup completado: $BACKUP_DIR/shocktv_$DATE.db"
```

**Hacer ejecutable y agendar:**
```bash
sudo chmod +x /usr/local/bin/backup-shocktv.sh

# Agregar a crontab (cada día a las 2 AM)
sudo crontab -e
# Añadir: 0 2 * * * /usr/local/bin/backup-shocktv.sh
```

### Step 6: Monitoreo

```bash
# Instalar herramientas de monitoreo
sudo apt install htop curl

# Crear script de health check
nano /usr/local/bin/check-shocktv.sh
```

**Contenido:**
```bash
#!/bin/bash
URL="https://shocktv.example.com/admin/"
RESPONSE=$(curl -s -o /dev/null -w "%{http_code}" $URL)

if [ $RESPONSE -eq 200 ]; then
    echo "✓ ShockTV is UP"
else
    echo "✗ ShockTV is DOWN (HTTP $RESPONSE)"
    # Notificar por email, Slack, etc
fi
```

### Step 7: Configuración de Producción

**Editar config/api.php si es necesario:**
```php
// Si usas tu propio API key de TMDB
define('TMDB_API_KEY', 'tu_api_key_aqui');

// Cambiar si necesitas otro idioma
define('LANGUAGE', 'es-MX');
```

### Step 8: Verificación Final

```bash
# Verificar permisos
ls -la /var/www/shocktv/
ls -la /var/www/shocktv/database/

# Probar conexión BD
php -r "require 'config/db.php'; echo getDB() ? 'OK' : 'FAIL';"

# Verificar logs
tail -f /var/log/apache2/shocktv_error.log  # Apache
tail -f /var/log/nginx/shocktv_error.log    # Nginx

# Acceder a admin
curl -u admin:admin123 https://shocktv.example.com/admin/
```

## 🔒 Checklist de Seguridad

- [ ] HTTPS activado con certificado válido
- [ ] Contraseña admin cambiada
- [ ] `/config` y `/database` protegidos
- [ ] `init-db.php` e `install.php` eliminar o proteger
- [ ] Firewalls configurados
- [ ] Backups automáticos activos
- [ ] Monitoreo activo
- [ ] SSL/TLS A+ (testear en ssllabs.com)
- [ ] Headers de seguridad configurados
- [ ] Rate limiting habilitado

## 📊 Headers de Seguridad (opcional pero recomendado)

**Apache (.htaccess):**
```apache
<IfModule mod_headers.c>
    Header set X-Content-Type-Options "nosniff"
    Header set X-Frame-Options "SAMEORIGIN"
    Header set X-XSS-Protection "1; mode=block"
    Header set Referrer-Policy "strict-origin-when-cross-origin"
</IfModule>
```

**Nginx:**
```nginx
add_header X-Content-Type-Options "nosniff";
add_header X-Frame-Options "SAMEORIGIN";
add_header X-XSS-Protection "1; mode=block";
add_header Referrer-Policy "strict-origin-when-cross-origin";
```

## 🐛 Troubleshooting en Producción

**"Database locked"**
- Verificar permisos `/database`
- Revisar procesos PHP activos
- Reiniciar PHP-FPM: `sudo systemctl restart php8.1-fpm`

**"Connection refused to TMDB"**
- Verificar conexión a internet
- Firewall permitir conexiones salientes
- Verificar API key

**"Permission denied"**
- `chmod 755 directory`
- `chmod 644 files`
- Revisar propietario (chown)

**"Out of memory"**
- Aumentar `memory_limit` en php.ini
- Optimizar queries
- Implementar paginación

## 📈 Optimización

```php
// En php.ini para producción
display_errors = Off
log_errors = On
error_log = /var/log/php_errors.log

memory_limit = 256M
max_execution_time = 30
upload_max_filesize = 100M

session.cookie_secure = On
session.cookie_httponly = On
session.cookie_samesite = Strict
```

## 🔄 Actualizar Código

```bash
cd /var/www/shocktv
git fetch origin
git pull origin main

# Si hay cambios en BD
php init-db.php

# Reiniciar servicios
sudo systemctl reload apache2  # O nginx
```

## 📞 Soporte

- Documentación: README.md
- Issues: GitHub Issues
- Email: admin@example.com

---

**Nota:** Este es un guía general. Adaptar según tu infraestructura específica.
