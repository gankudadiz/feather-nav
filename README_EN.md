# Personal Navigation Site

[中文](README.md)

A lightweight personal navigation website built with Flight PHP + Tailwind CSS + Alpine.js.

## Features

- ✅ **Lightweight**: Flight PHP framework, extremely low memory usage
- ✅ **Responsive Design**: Uses Tailwind CSS
- ✅ **Simple Frontend**: Alpine.js for interactivity without complex frameworks
- ✅ **User Authentication**: Simple and secure login system
- ✅ **Category Management**: Support for categories
- ✅ **Search Functionality**: Quickly find links
- ✅ **Icon Support**: Support for custom icons and auto-fetching

## Tech Stack

- **Backend**: Flight PHP 3.17 + PHP 8.3
- **Frontend**: Tailwind CSS 3.4 + Alpine.js 3.13 + Vite 5.0
- **Database**: MySQL 5.7/8.0
- **Build Tool**: Vite

## Quick Start

### Method 1: Quick Preview (Recommended)

If you just want to experience this project quickly without complex configuration, use our provided preview scripts:

**Windows Users:**
```bash
start-preview.bat
```

**Linux/Mac Users:**
```bash
chmod +x start-preview.sh
./start-preview.sh
```

This script will automatically detect the environment, install dependencies, build frontend assets, and start the local preview server.

### Method 2: Manual Installation

#### Requirements

- PHP 8.1+
- MySQL 5.7+
- Node.js 16+ (for developing/building frontend assets)

#### Installation Steps

1. **Clone the Project**
   ```bash
   git clone <repository-url>
   cd feather-nav
   ```

2. **Install PHP Dependencies**
   ```bash
   composer install
   ```

3. **Install Frontend Dependencies**
   ```bash
   npm install
   ```

4. **Configure Database**
   - Copy configuration template:
     ```bash
     cp .env.example .env
     ```
   - Edit `.env` file, configure database connection and admin credentials (`ADMIN_USERNAME`, `ADMIN_PASSWORD`)
   - Run initialization script:
     ```bash
     php scripts/setup_db.php
     ```

5. **Build Frontend Assets**
   ```bash
   npm run build
   ```

6. **Start Development Server**
   ```bash
   php -S localhost:8080 -t public
   ```

7. **Access the Website**
   - Home: http://localhost:8080
   - Admin Panel: http://localhost:8080/admin
   - Default Login: admin / admin123

### Development Mode

You can use Vite's Hot Module Replacement (HMR) for real-time CSS and JS updates during development:

#### Method 1: Use Start Script (Recommended)

**Windows Users:**
```bash
start-dev.bat
```

**Linux/Mac Users:**
```bash
chmod +x start-dev.sh
./start-dev.sh
```

#### Method 2: Manually Start Two Services

```bash
# Terminal 1: Start Frontend Dev Server (HMR enabled)
npm run dev

# Terminal 2: Start PHP Server
php -S localhost:8080 -t public
```

#### Service URLs

- **Website Home**: http://localhost:8080
- **Admin Panel**: http://localhost:8080/admin
- **Vite Dev Server**: http://localhost:5173 (for frontend assets)

#### HMR Details

- Modify CSS (`resources/css/app.css`): Browser refreshes automatically
- Modify JS (`resources/js/main.js`): Browser refreshes automatically
- Modify PHP: Requires manual browser refresh
- Modify Views (`resources/views/*.php`): Requires manual browser refresh

## Project Structure

```
feather-nav/
├── app/                      # Application Code
│   ├── Controllers/          # Controllers
│   ├── Helpers/             # Helpers
│   └── Middleware/          # Middleware
├── config/                  # Configuration Files
├── database/                # Database Related
│   └── init.sql            # Initialization Script
├── public/                  # Web Root
│   ├── assets/             # Static Assets
│   ├── index.php           # Entry Point
│   └── .htaccess           # URL Rewrite Rules
├── resources/              # Frontend Resources
│   ├── css/               # Stylesheets
│   ├── js/                # JavaScript Files
│   └── views/             # View Templates
├── storage/               # Storage Directory
│   └── logs/             # Log Files
├── vendor/               # Composer Dependencies
├── node_modules/         # NPM Dependencies
├── .env                  # Environment Variables
├── composer.json         # PHP Dependency Config
└── package.json          # Frontend Dependency Config
```

## Production Deployment

1. **Build Frontend Assets**
   ```bash
   npm run build
   ```

2. **Configure Server**
   - Point Web Root to `public/`
   - Ensure `.htaccess` is effective (Apache)
   - Or configure Nginx rewrite rules

3. **Set Environment Variables**
   - Copy `.env.example` to `.env`
   - Modify production configuration

4. **Security Recommendations**
   - Change default admin password
   - Enable HTTPS
   - Regularly backup database

## Custom Configuration

### Change Default User

Modify `ADMIN_USERNAME` and `ADMIN_PASSWORD` in `.env`, then re-run `php scripts/setup_db.php`, or update directly in the database:

```sql
UPDATE users SET password = 'your_hashed_password' WHERE username = 'admin';
```

### Add More Features

- Theme Switching: Add dark mode support
- Import/Export: Support browser bookmark import
- Tag System: Add tags to links
- Sorting: Custom sorting for categories and links

## License

MIT License

## Contributing

Issues and Pull Requests are welcome!

## Contact

If you have any questions, please provide feedback via GitHub Issues.
