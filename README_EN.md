# Personal Navigation Site

[中文](README.md)

A lightweight personal navigation website built with Flight PHP + Tailwind CSS + Alpine.js + Vite.

## Features

- ✅ **Lightweight**: Flight PHP framework, extremely low memory usage
- ✅ **Responsive Design**: Tailwind CSS, mobile-friendly
- ✅ **Progressive Enhancement**: Alpine.js for interactivity, no heavy frontend framework
- ✅ **User Authentication**: Simple and secure login system
- ✅ **Category Management**: CRUD with automatic link association check before deletion
- ✅ **Link Management**: VPN markers, auto-fetch / manual icon upload
- ✅ **Search & Filter**: Quick link search, filter by status (e.g., "No Icon")
- ✅ **Data Statistics**: Admin dashboard with real-time link, category, and exception stats
- ✅ **Import/Export**: JSON backup & browser HTML bookmark import with preview
- ✅ **Audit Logs**: Track key admin operations for traceability
- ✅ **SPA Experience**: Hash-based routing with browser back/forward support

## Tech Stack

- **Backend**: Flight PHP 3.17 + PHP 8.1+
- **Frontend**: Tailwind CSS 3.4 + Alpine.js 3.13
- **Build Tool**: Vite 5.x
- **Database**: MySQL 5.7 / 8.0 or SQLite 3

## Requirements

| Dependency | Version | Notes |
|------------|---------|-------|
| PHP | ≥ 8.1 | Extension: `fileinfo`, plus `pdo_mysql` or `pdo_sqlite` depending on your database |
| MySQL | ≥ 5.7 | Required when using MySQL |
| SQLite | 3.x | Required when using SQLite via PHP `pdo_sqlite` |
| Node.js | ≥ 16 | For frontend build & dev HMR |
| Composer | Latest | PHP dependency manager |

## Quick Start

Use the provided launch scripts — they auto-detect your platform.

### Method 1: Preview Mode (no dev server)

Builds frontend assets and starts PHP server. Ideal for a quick tour.

**Windows (native):**

```batch
start-preview.bat
```

**Linux / macOS / WSL2:**

```bash
chmod +x start-preview.sh
./start-preview.sh
```

The script installs dependencies, builds assets, and starts the server. Open `http://127.0.0.1:8100` in your browser.

> **WSL2 users**: The script automatically binds to `0.0.0.0` so the Windows browser can reach the server.

### Method 2: Manual Installation

1. **Clone the project**

   ```bash
   git clone <repository-url>
   cd feather-nav
   ```

2. **Install PHP dependencies**

   ```bash
   composer install
   ```

3. **Install frontend dependencies**

   ```bash
   npm install
   ```

4. **Configure database**

   ```bash
   cp .env.example .env
   ```

   Edit `.env` with your database and admin credentials. MySQL is the default:

   ```ini
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=personal_nav
   DB_USERNAME=root
   DB_PASSWORD=your_password

   ADMIN_USERNAME=admin
   ADMIN_PASSWORD=admin123
   ```

   To use SQLite instead:

   ```ini
   DB_CONNECTION=sqlite
   DB_SQLITE_PATH=storage/database/personal_nav.sqlite

   ADMIN_USERNAME=admin
   ADMIN_PASSWORD=admin123
   ```

   Initialize the database:

   ```bash
   php scripts/setup_db.php
   ```

   To migrate existing MySQL data into SQLite, keep the MySQL settings and `DB_SQLITE_PATH` configured, then run:

   ```bash
   php scripts/mysql_to_sqlite.php --fresh
   ```

5. **Build frontend assets**

   ```bash
   npm run build
   ```

6. **Start the server**

   | Platform | Command |
   |----------|---------|
   | Linux / macOS | `php -S 127.0.0.1:8100 -t public` |
   | Windows (native) | `php -S 127.0.0.1:8100 -t public` |
   | **WSL2** | `php -S 0.0.0.0:8100 -t public` |

   > WSL2 must bind to `0.0.0.0`, otherwise the Windows browser cannot connect.

7. **Access**

   - Home: `http://127.0.0.1:8100`
   - Admin Panel: `http://127.0.0.1:8100/admin`
   - Default credentials: `admin` / `admin123` (editable in `.env`)

## Development Mode

Vite's HMR provides live reload for CSS and JS changes.

### Use Launch Script (recommended)

Scripts auto-detect your platform and choose the correct bind address.

**Windows (native):**

```batch
start-dev.bat
```

**Linux / macOS / WSL2:**

```bash
chmod +x start-dev.sh
./start-dev.sh
```

### Manual Start

Run two services in separate terminals:

```bash
# Terminal 1: Vite dev server (HMR)
npm run dev

# Terminal 2: PHP server
# Linux / macOS / Windows native:
php -S 127.0.0.1:8100 -t public

# WSL2:
php -S 0.0.0.0:8100 -t public
```

### Service URLs

| Service | Standard | WSL2 |
|---------|----------|------|
| Homepage | `http://127.0.0.1:8100` | `http://127.0.0.1:8100` (host browser) |
| Admin Panel | `http://127.0.0.1:8100/admin` | Same |
| Vite Dev Server | `http://127.0.0.1:5173` | `http://<WSL2-IP>:5173` (auto-handled) |

### HMR Details

| File Type | Path | Behavior |
|-----------|------|----------|
| CSS | `resources/css/app.css` | Auto-reload |
| JS | `resources/js/main.js` | Auto-reload |
| PHP | `app/**/*.php` | Manual refresh |
| Views | `resources/views/*.php` | Manual refresh |

## WSL2 Notes

WSL2 has its own network stack, isolated from the Windows host. Key implications:

### Why bind to `0.0.0.0`?

- `127.0.0.1` inside WSL2 refers to WSL2 itself — the Windows browser cannot reach it
- Binding to `0.0.0.0` allows Windows to forward `127.0.0.1:8100` into WSL2 automatically

### Why Vite assets need the WSL2 IP?

- The Vite dev server runs inside WSL2 (port `5173`)
- The Windows browser must load `@vite/client` and JS/CSS modules via WSL2's virtual machine IP
- This project's `AssetHelper` and CSP are already configured to auto-detect the WSL2 IP — no manual setup needed

### Recommendation

Use the provided launch scripts (`start-dev.sh` / `start-preview.sh`) — they handle everything automatically.

If starting manually, always use:
```bash
php -S 0.0.0.0:8100 -t public   # NOT 127.0.0.1
```

## Project Structure

```
feather-nav/
├── app/                      # Application Code
│   ├── Controllers/          # Controllers
│   ├── Helpers/              # Helpers (incl. AssetHelper)
│   └── Middleware/           # Middleware
├── config/                   # Configuration
├── database/                 # Database Scripts
│   └── init.sql              # Initialization SQL
├── public/                   # Web Root
│   ├── assets/               # Build output (npm run build)
│   └── index.php             # Entry Point
├── resources/                # Frontend Source
│   ├── css/                  # Tailwind CSS
│   ├── js/                   # JavaScript
│   └── views/                # PHP Templates
│       ├── admin/            # Admin views
│       ├── auth/             # Login views
│       ├── home.php          # Homepage
│       └── layout.php        # Shared layout
├── scripts/                  # Utility Scripts
│   └── setup_db.php          # Database initializer
├── storage/                  # Runtime Storage
│   └── logs/                 # Logs
├── start-dev.sh              # Dev mode (Linux/macOS/WSL2)
├── start-dev.bat             # Dev mode (Windows)
├── start-preview.sh          # Preview mode (Linux/macOS/WSL2)
├── start-preview.bat         # Preview mode (Windows)
├── .env.example              # Environment template
├── composer.json             # PHP dependencies
├── package.json              # Frontend dependencies
└── vite.config.js            # Vite configuration
```

## Production Deployment

1. **Build frontend assets**

   ```bash
   npm run build
   ```

2. **Configure web server**

   Point web root to `public/`. Ensure URL rewriting is set up:

   - **Apache**: `.htaccess` included
   - **Nginx**:

   ```nginx
   location / {
       try_files $uri $uri/ /index.php?$query_string;
   }
   ```

3. **Set environment variables**

   ```bash
   cp .env.example .env
   ```

   Set `APP_ENV=production` and `APP_DEBUG=false`.

4. **Security**

   - Change default admin password
   - Enable HTTPS
   - Schedule regular database backups

## FAQ

### Browser can't connect (WSL2)?

Make sure the PHP server is bound to `0.0.0.0`, not `127.0.0.1`. Use the launch scripts to avoid this.

### Page loads but CSS/JS are missing (WSL2)?

This is a Vite asset path issue. Ensure:
1. Your code is up to date (`AssetHelper` has built-in WSL2 IP detection)
2. Check browser console for CSP errors — the CSP should allow the correct WSL2 IP

### Port already in use?

Edit `PHP_PORT` and `VITE_PORT` in the launch scripts, and update `APP_URL` in `.env` accordingly.

## License

MIT License

## Contributing

Issues and Pull Requests are welcome!
