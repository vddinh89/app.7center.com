# Docker Environment for VietGo Clone Project

This Docker setup provides a complete development environment for the Laravel-based VietGo clone project with all the required specifications.

## Services Included

- **PHP 8.2-FPM** with all required extensions
- **Nginx** web server with optimized configuration
- **MySQL 8.0** with proper charset and collation settings
- **Redis** for caching and sessions
- **phpMyAdmin** for database management

## Requirements Met

### Database Requirements ✅
- MySQL 8.0 (compatible with 5.7+ requirement)
- Full privilege database user
- Collation set to `utf8mb4_0900_ai_ci` (highest preference)
- `max_user_connections = 100`
- `max_connections = 200`

### PHP Requirements ✅
- PHP 8.2 with all required extensions:
  - BCMath, Ctype, cURL, DOM, Fileinfo, Filter, Hash, JSON
  - Mbstring, OpenSSL, PCRE, PDO, Session, Tokenizer, XML
  - GD Extension (version 2.0+) and Imagick (version 6.5.7+)
  - PHP Zip Archive
- All default PHP functions enabled (exec, escapeshellarg, etc.)

### PHP.ini Configuration ✅
- `open_basedir` is disabled as required
- Memory limit set to 512M
- Upload limits set to 100M
- Proper timezone configuration

### File Permissions ✅
- Bootstrap directory: 775
- Storage directory: 775 (recursive)

## Usage

### 1. Start the Environment
```bash
docker-compose up -d
```

### 2. Install Dependencies
```bash
docker-compose exec app composer install
```

### 3. Set Environment Variables
Copy the `.env.example` to `.env` and update database credentials:
```
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=vietgo_db
DB_USERNAME=vietgo_user
DB_PASSWORD=vietgo_password
```

### 4. Generate Application Key
```bash
docker-compose exec app php artisan key:generate
```

### 5. Run Migrations
```bash
docker-compose exec app php artisan migrate
```

## Access Points

- **Application**: http://localhost
- **phpMyAdmin**: http://localhost:8080
- **Database**: localhost:3306
- **Redis**: localhost:6379

## Database Credentials

- **Root Password**: `root_password`
- **Database**: `vietgo_db`
- **Username**: `vietgo_user`
- **Password**: `vietgo_password`

## Commands

### Access Application Container
```bash
docker-compose exec app bash
```

### View Logs
```bash
docker-compose logs -f app
docker-compose logs -f nginx
docker-compose logs -f db
```

### Stop Services
```bash
docker-compose down
```

### Rebuild Images
```bash
docker-compose up --build -d
```

## File Structure

```
docker/
├── nginx/
│   ├── nginx.conf      # Main Nginx configuration
│   └── default.conf    # Site-specific configuration
├── php/
│   └── php.ini         # PHP configuration with all requirements
└── mysql/
    └── my.cnf          # MySQL configuration
```

## Notes

- The setup uses PHP-FPM with Nginx for optimal performance
- All required PHP extensions are installed and enabled
- Database charset and collation are properly configured
- File permissions are automatically set during container startup
- Redis is available for caching and session storage

## Troubleshooting

If you encounter permission issues:
```bash
docker-compose exec app chown -R www-data:www-data /var/www
docker-compose exec app chmod -R 775 /var/www/storage
docker-compose exec app chmod -R 775 /var/www/bootstrap/cache
```
