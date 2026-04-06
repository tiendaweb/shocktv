# ShockTV v2.0 - Complete Documentation Index

## 📚 Documentation Files

### Getting Started
1. **[QUICKSTART.md](QUICKSTART.md)** - ⚡ Start in 30 seconds
   - Installation
   - First steps
   - Common tasks
   - Basic troubleshooting

2. **[README.md](README.md)** - 📖 Complete Documentation
   - Project overview
   - Architecture
   - Installation details
   - Feature documentation
   - Database reference
   - Configuration guide
   - Security details
   - Advanced troubleshooting

### Deployment & Operations
3. **[DEPLOYMENT.md](DEPLOYMENT.md)** - 🚀 Production Setup
   - Server configuration (Apache/Nginx)
   - SSL/HTTPS setup
   - Security hardening
   - Backup & monitoring
   - Optimization tips
   - Troubleshooting

### Server Configuration
- **.htaccess** - Apache rewrite rules
- **nginx.conf.example** - Nginx server block example

### Scripts
- **init-db.php** - Database initialization
- **install.php** - Interactive installation wizard

---

## 🎯 Quick Navigation

### I want to...

#### **Start immediately**
→ Run `php init-db.php` then visit `/admin/` with admin/admin123

#### **Understand the architecture**
→ Read [README.md](README.md#-structure-del-proyecto)

#### **Deploy to production**
→ Follow [DEPLOYMENT.md](DEPLOYMENT.md)

#### **Add movies/series**
→ See [QUICKSTART.md](QUICKSTART.md#-agregar-tu-primera-película)

#### **Manage providers**
→ See [QUICKSTART.md](QUICKSTART.md#-agregar-proveedor-de-streaming) or [README.md](README.md#-proveedores)

#### **Change admin password**
→ Run `php install.php`

#### **Fix a problem**
→ Check [README.md](README.md#-troubleshooting) or [DEPLOYMENT.md](DEPLOYMENT.md#troubleshooting-en-producción)

---

## 📋 Feature Documentation

### Frontend
- Dynamic movie loading from database
- Live TMDB search
- Provider selection system
- Episode list for series
- Spanish language forced (es-MX)
- Responsive design (TV, Desktop, Tablet, Mobile)

See: [README.md#-interfaz-dinámica](README.md)

### Admin Panel
- User login with bcrypt
- Dashboard with statistics
- Movie/Series CRUD
- Provider management
- Live TMDB search and import
- Paginatyed listings

See: [README.md#-gestión-de-películas](README.md)

### APIs
- `/api/search.php` - Search TMDB
- `/api/getMovies.php` - Get stored movies
- `/api/getProviders.php` - Get active providers

See: [README.md#-api-interna](README.md)

### Database
- SQLite (no external dependencies)
- 4 tables: admin_users, movies, providers, search_cache
- Automatic indexes for performance
- Caching layer for searches

See: [README.md#-base-de-datos](README.md)

---

## 🔐 Security Features

✓ Bcrypt password hashing
✓ PDO prepared statements
✓ Session timeout (30 min)
✓ Input validation
✓ File protection (.htaccess)
✓ CSRF prevention

Details: [README.md#-seguridad](README.md)

---

## 🛠️ Configuration

### TMDB API Key
**File:** `config/api.php`
```php
define('TMDB_API_KEY', '2628b2d65ef5a50b08d992e0a7c2de56');
define('LANGUAGE', 'es-MX'); // Always Spanish
```

### Database
**File:** `config/db.php`
```php
define('DB_PATH', __DIR__ . '/../database/shocktv.db');
```

### Admin Credentials
**Change with:** `php install.php`

More details: [README.md#⚙️-configuración](README.md)

---

## 📊 Project Statistics

- **Files:** 25+ source files
- **Code:** 4,500+ lines
- **Database:** SQLite with 4 tables
- **Languages:** PHP, HTML, CSS, JavaScript, SQL
- **API Integration:** The Movie Database (TMDB)
- **Frontend:** Tailwind CSS + Font Awesome

---

## 🗂️ File Structure

```
shocktv/
├── Admin Panel
│   ├── admin/index.php (login)
│   ├── admin/dashboard.php (stats)
│   ├── admin/movies.php (CRUD)
│   ├── admin/providers.php (streaming servers)
│   └── admin/auth.php (authentication)
│
├── Frontend
│   ├── index.php (main app with DB)
│   └── index.html (original backup)
│
├── APIs
│   ├── api/search.php
│   ├── api/getMovies.php
│   └── api/getProviders.php
│
├── Configuration
│   ├── config/db.php
│   ├── config/api.php
│   └── config/constants.php
│
├── Database
│   ├── database/schema.sql
│   └── database/shocktv.db
│
├── Utils
│   ├── init-db.php
│   ├── install.php
│   └── .htaccess
│
└── Documentation
    ├── README.md
    ├── QUICKSTART.md
    ├── DEPLOYMENT.md
    ├── DOCUMENTATION_INDEX.md
    └── nginx.conf.example
```

---

## 📱 Supported Devices

- 📺 Smart TV (1.4x zoom)
- 🖥️ Desktop (full layout)
- 📱 Mobile (responsive)
- 📲 Tablet (grid adapts)

---

## 🌍 Languages

- **UI:** Spanish (Spain + Latin America)
- **API:** Spanish forced (es-MX)
- **Database:** Spanish content
- **Documentation:** Spanish + English

---

## ✅ Requirements

- PHP 7.4+
- SQLite (included in PHP)
- cURL (included in PHP)
- Apache (mod_rewrite) or Nginx

---

## 🔄 Update Checklist

When updating code:
1. Backup database: `cp database/shocktv.db database/shocktv.db.bak`
2. Update files from git
3. Test locally first
4. Run database migrations if needed
5. Deploy to production

---

## 📞 Support Resources

**Documentation:**
- README.md - Full reference
- QUICKSTART.md - Fast start
- DEPLOYMENT.md - Production guide

**Code Comments:**
- IMPORTANTE markers for critical code
- Spanish comments where needed

**Help Section:**
- Troubleshooting in README.md
- Production issues in DEPLOYMENT.md

---

## 🎓 Learning Path

### Beginner
1. Read QUICKSTART.md
2. Install with `php init-db.php`
3. Add 5 movies via admin
4. Test providers

### Intermediate
1. Read README.md completely
2. Modify admin interface
3. Create custom provider
4. Add user features

### Advanced
1. Study code structure
2. Implement new features
3. Optimize database queries
4. Deploy to production

---

## 🚀 Performance Tips

- Search results cached 24 hours
- Database indexed for fast queries
- Lazy load providers
- Pagination for large lists
- Gzip compression (configure in server)

See: [DEPLOYMENT.md#optimización](DEPLOYMENT.md)

---

## 📈 Future Enhancements

- User accounts & favorites
- Watch history
- Movie ratings
- Comments system
- Email notifications
- API for mobile apps
- Dark mode toggle
- Multi-language support

---

## 📄 License

Private project - ShockTV v2.0

---

## 👨‍💼 Maintenance

### Weekly
- Check server logs
- Monitor database size
- Verify backups

### Monthly
- Review admin logs
- Update TMDB cache
- Test disaster recovery

### Quarterly
- Update dependencies
- Review security
- Performance audit

---

**Last Updated:** 2024  
**Version:** 2.0  
**Status:** Production Ready ✅
