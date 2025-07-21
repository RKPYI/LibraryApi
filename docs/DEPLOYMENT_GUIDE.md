# Deployment Guide

This guide covers deployment strategies, configurations, and best practices for the Library Management System API.

## Table of Contents

1. [Deployment Overview](#deployment-overview)
2. [Environment Setup](#environment-setup)
3. [Production Configuration](#production-configuration)
4. [Docker Deployment](#docker-deployment)
5. [Cloud Deployment](#cloud-deployment)
6. [Security Checklist](#security-checklist)
7. [Performance Optimization](#performance-optimization)
8. [Monitoring & Logging](#monitoring--logging)
9. [Backup & Recovery](#backup--recovery)
10. [CI/CD Pipeline](#cicd-pipeline)

## Deployment Overview

### Supported Deployment Methods

- **Traditional Server**: Apache/Nginx + PHP-FPM
- **Docker**: Containerized deployment
- **Cloud Platforms**: AWS, Google Cloud, Azure
- **Platform-as-a-Service**: Heroku, DigitalOcean App Platform

### System Requirements

#### Minimum Requirements
- **CPU**: 1 vCPU
- **RAM**: 1GB
- **Storage**: 10GB SSD
- **PHP**: 8.2+
- **Database**: MySQL 8.0+ or PostgreSQL 13+
- **Web Server**: Nginx 1.18+ or Apache 2.4+

#### Recommended Requirements
- **CPU**: 2+ vCPUs
- **RAM**: 4GB+
- **Storage**: 50GB+ SSD
- **Load Balancer**: For high availability
- **Cache**: Redis or Memcached
- **CDN**: For static assets

## Environment Setup

### Production Environment File

Create `.env.production`:

```env
# Application
APP_NAME="Library Management API"
APP_ENV=production
APP_KEY=base64:YOUR_32_CHARACTER_SECRET_KEY
APP_DEBUG=false
APP_URL=https://api.yourdomain.com

# Database
DB_CONNECTION=mysql
DB_HOST=your-db-host
DB_PORT=3306
DB_DATABASE=library_production
DB_USERNAME=library_user
DB_PASSWORD=secure_password_here

# Cache
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# Redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Mail
MAIL_MAILER=smtp
MAIL_HOST=your-mail-server
MAIL_PORT=587
MAIL_USERNAME=your-email@domain.com
MAIL_PASSWORD=your-email-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="Library API"

# Logging
LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

# Security
SANCTUM_STATEFUL_DOMAINS=yourdomain.com,api.yourdomain.com
SESSION_SECURE_COOKIE=true

# File Storage
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=your-aws-access-key
AWS_SECRET_ACCESS_KEY=your-aws-secret-key
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=your-s3-bucket

# API Documentation
L5_SWAGGER_CONST_HOST=https://api.yourdomain.com
```

### Secrets Management

```bash
# Generate secure application key
php artisan key:generate --show

# Use environment-specific configuration
export APP_KEY="base64:your-generated-key"
export DB_PASSWORD="your-secure-db-password"
```

## Production Configuration

### Web Server Configuration

#### Nginx Configuration

Create `/etc/nginx/sites-available/library-api`:

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name api.yourdomain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name api.yourdomain.com;
    root /var/www/library-api/public;

    # SSL Configuration
    ssl_certificate /path/to/your/certificate.crt;
    ssl_certificate_key /path/to/your/private.key;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-RSA-AES256-GCM-SHA512:DHE-RSA-AES256-GCM-SHA512:ECDHE-RSA-AES256-GCM-SHA384:DHE-RSA-AES256-GCM-SHA384;
    ssl_prefer_server_ciphers off;

    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    add_header Content-Security-Policy "default-src 'self' http: https: data: blob: 'unsafe-inline'" always;
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;

    # General Configuration
    index index.php;
    charset utf-8;

    # Rate Limiting
    limit_req_zone $binary_remote_addr zone=api:10m rate=60r/m;
    limit_req zone=api burst=20 nodelay;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
        
        # Security
        fastcgi_param HTTP_PROXY "";
        fastcgi_read_timeout 300;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Cache static assets
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
```

#### Apache Configuration

Create `/etc/apache2/sites-available/library-api.conf`:

```apache
<VirtualHost *:80>
    ServerName api.yourdomain.com
    Redirect permanent / https://api.yourdomain.com/
</VirtualHost>

<VirtualHost *:443>
    ServerName api.yourdomain.com
    DocumentRoot /var/www/library-api/public

    # SSL Configuration
    SSLEngine on
    SSLCertificateFile /path/to/your/certificate.crt
    SSLCertificateKeyFile /path/to/your/private.key
    SSLProtocol all -SSLv3 -TLSv1 -TLSv1.1
    SSLCipherSuite ECDHE-ECDSA-AES256-GCM-SHA384:ECDHE-RSA-AES256-GCM-SHA384:ECDHE-ECDSA-CHACHA20-POLY1305

    # Security Headers
    Header always set X-Frame-Options "SAMEORIGIN"
    Header always set X-XSS-Protection "1; mode=block"
    Header always set X-Content-Type-Options "nosniff"
    Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"

    <Directory /var/www/library-api/public>
        AllowOverride All
        Require all granted
    </Directory>

    # Hide sensitive files
    <Files ".env*">
        Require all denied
    </Files>

    ErrorLog ${APACHE_LOG_DIR}/library-api_error.log
    CustomLog ${APACHE_LOG_DIR}/library-api_access.log combined
</VirtualHost>
```

### PHP Configuration

Update `php.ini` for production:

```ini
; Security
expose_php = Off
display_errors = Off
display_startup_errors = Off
log_errors = On

; Performance
opcache.enable = 1
opcache.memory_consumption = 256
opcache.interned_strings_buffer = 16
opcache.max_accelerated_files = 20000
opcache.validate_timestamps = 0
opcache.save_comments = 1
opcache.fast_shutdown = 1

; Memory
memory_limit = 512M
post_max_size = 32M
upload_max_filesize = 32M

; Execution
max_execution_time = 60
max_input_time = 60
```

### Database Configuration

#### MySQL Production Setup

```sql
-- Create production database and user
CREATE DATABASE library_production CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'library_user'@'%' IDENTIFIED BY 'secure_password_here';
GRANT SELECT, INSERT, UPDATE, DELETE ON library_production.* TO 'library_user'@'%';
FLUSH PRIVILEGES;

-- Optimize MySQL for production
SET GLOBAL innodb_buffer_pool_size = 1073741824; -- 1GB
SET GLOBAL innodb_log_file_size = 268435456; -- 256MB
SET GLOBAL max_connections = 200;
```

#### PostgreSQL Production Setup

```sql
-- Create production database and user
CREATE DATABASE library_production;
CREATE USER library_user WITH PASSWORD 'secure_password_here';
GRANT ALL PRIVILEGES ON DATABASE library_production TO library_user;

-- Optimize PostgreSQL
ALTER SYSTEM SET shared_buffers = '256MB';
ALTER SYSTEM SET effective_cache_size = '1GB';
ALTER SYSTEM SET work_mem = '4MB';
SELECT pg_reload_conf();
```

## Docker Deployment

### Dockerfile

```dockerfile
FROM php:8.2-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libxml2-dev \
    zip \
    unzip \
    postgresql-dev \
    mysql-client

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql pdo_pgsql gd xml

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Create application directory
WORKDIR /var/www

# Copy application files
COPY . .

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Set permissions
RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www/storage

# Expose port
EXPOSE 9000

CMD ["php-fpm"]
```

### Docker Compose

```yaml
version: '3.8'

services:
  app:
    build: .
    container_name: library-api
    restart: unless-stopped
    working_dir: /var/www
    volumes:
      - ./:/var/www
    networks:
      - library-network
    depends_on:
      - database
      - redis

  nginx:
    image: nginx:alpine
    container_name: library-nginx
    restart: unless-stopped
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - ./:/var/www
      - ./docker/nginx/conf.d:/etc/nginx/conf.d
      - ./docker/nginx/ssl:/etc/nginx/ssl
    networks:
      - library-network
    depends_on:
      - app

  database:
    image: mysql:8.0
    container_name: library-db
    restart: unless-stopped
    environment:
      MYSQL_DATABASE: library_production
      MYSQL_USER: library_user
      MYSQL_PASSWORD: secure_password
      MYSQL_ROOT_PASSWORD: root_password
    volumes:
      - db_data:/var/lib/mysql
    networks:
      - library-network

  redis:
    image: redis:alpine
    container_name: library-redis
    restart: unless-stopped
    networks:
      - library-network

volumes:
  db_data:

networks:
  library-network:
    driver: bridge
```

### Deployment Commands

```bash
# Build and start containers
docker-compose up -d --build

# Run migrations
docker-compose exec app php artisan migrate --force

# Optimize application
docker-compose exec app php artisan config:cache
docker-compose exec app php artisan route:cache
docker-compose exec app php artisan view:cache

# Generate API documentation
docker-compose exec app php artisan l5-swagger:generate
```

## Cloud Deployment

### AWS Deployment

#### Using AWS Elastic Beanstalk

1. **Prepare Application**
```bash
# Create deployment package
zip -r library-api.zip . -x "*.git*" "node_modules/*" "tests/*"
```

2. **Elastic Beanstalk Configuration**

`.ebextensions/01-php.config`:
```yaml
option_settings:
  aws:elasticbeanstalk:container:php:phpini:
    memory_limit: 512M
    post_max_size: 32M
    upload_max_filesize: 32M
    max_execution_time: 60
  aws:elasticbeanstalk:application:environment:
    APP_ENV: production
    APP_DEBUG: false
    CACHE_DRIVER: redis
```

3. **Database Setup (RDS)**
```bash
# Configure RDS MySQL instance
aws rds create-db-instance \
    --db-name library_production \
    --db-instance-identifier library-db \
    --db-instance-class db.t3.micro \
    --engine mysql \
    --master-username admin \
    --master-user-password secure_password \
    --allocated-storage 20
```

#### Using AWS ECS/Fargate

`task-definition.json`:
```json
{
  "family": "library-api",
  "networkMode": "awsvpc",
  "requiresCompatibilities": ["FARGATE"],
  "cpu": "256",
  "memory": "512",
  "executionRoleArn": "arn:aws:iam::account:role/ecsTaskExecutionRole",
  "containerDefinitions": [
    {
      "name": "library-api",
      "image": "your-account.dkr.ecr.region.amazonaws.com/library-api:latest",
      "portMappings": [
        {
          "containerPort": 9000,
          "protocol": "tcp"
        }
      ],
      "environment": [
        {
          "name": "APP_ENV",
          "value": "production"
        }
      ],
      "secrets": [
        {
          "name": "APP_KEY",
          "valueFrom": "arn:aws:secretsmanager:region:account:secret:library-api-secrets"
        }
      ]
    }
  ]
}
```

### Google Cloud Platform

#### Using Cloud Run

```yaml
# cloudbuild.yaml
steps:
  - name: 'gcr.io/cloud-builders/docker'
    args: ['build', '-t', 'gcr.io/$PROJECT_ID/library-api', '.']
  - name: 'gcr.io/cloud-builders/docker'
    args: ['push', 'gcr.io/$PROJECT_ID/library-api']
  - name: 'gcr.io/google.com/cloudsdktool/cloud-sdk'
    entrypoint: 'gcloud'
    args:
      - 'run'
      - 'deploy'
      - 'library-api'
      - '--image=gcr.io/$PROJECT_ID/library-api'
      - '--region=us-central1'
      - '--platform=managed'
      - '--allow-unauthenticated'
```

## Security Checklist

### Application Security

- [ ] **Environment Variables**: Store secrets in environment variables
- [ ] **HTTPS Only**: Force SSL/TLS connections
- [ ] **Security Headers**: Implement security headers
- [ ] **Input Validation**: Validate all user inputs
- [ ] **SQL Injection Protection**: Use parameterized queries
- [ ] **XSS Protection**: Escape output properly
- [ ] **CSRF Protection**: Enable CSRF middleware
- [ ] **Rate Limiting**: Implement API rate limiting
- [ ] **Authentication**: Secure token-based authentication
- [ ] **Authorization**: Role-based access control

### Infrastructure Security

- [ ] **Firewall**: Configure web application firewall
- [ ] **Database Security**: Restrict database access
- [ ] **File Permissions**: Set proper file permissions
- [ ] **Regular Updates**: Keep system packages updated
- [ ] **Backup Encryption**: Encrypt database backups
- [ ] **SSL Certificate**: Use valid SSL certificates
- [ ] **Monitoring**: Set up security monitoring
- [ ] **Audit Logs**: Enable comprehensive logging

### Security Configuration

```bash
# Set proper file permissions
chmod -R 755 /var/www/library-api
chmod -R 775 /var/www/library-api/storage
chmod -R 775 /var/www/library-api/bootstrap/cache
chmod 600 /var/www/library-api/.env

# Hide sensitive files
echo "deny from all" > /var/www/library-api/.env.htaccess
```

## Performance Optimization

### Application Optimization

```bash
# Cache configuration and routes
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Optimize autoloader
composer install --optimize-autoloader --no-dev

# Enable OPcache
php -d opcache.enable=1
```

### Database Optimization

```sql
-- Add indexes for frequently queried columns
CREATE INDEX idx_books_title ON books(title);
CREATE INDEX idx_books_author ON books(author);
CREATE INDEX idx_borrows_status ON borrows(status);
CREATE INDEX idx_borrows_user_id ON borrows(user_id);
CREATE INDEX idx_borrows_book_id ON borrows(book_id);
```

### Caching Strategy

```php
// config/cache.php
'stores' => [
    'redis' => [
        'driver' => 'redis',
        'connection' => 'cache',
        'lock_connection' => 'default',
    ],
],

// Cache frequently accessed data
Cache::remember('books.popular', 3600, function () {
    return Book::popular()->limit(10)->get();
});
```

## Monitoring & Logging

### Application Monitoring

```bash
# Install monitoring tools
composer require laravel/telescope --dev
php artisan telescope:install
php artisan migrate
```

### Log Configuration

```php
// config/logging.php
'channels' => [
    'production' => [
        'driver' => 'stack',
        'channels' => ['daily', 'slack'],
        'ignore_exceptions' => false,
    ],
    
    'daily' => [
        'driver' => 'daily',
        'path' => storage_path('logs/laravel.log'),
        'level' => env('LOG_LEVEL', 'info'),
        'days' => 14,
    ],
    
    'slack' => [
        'driver' => 'slack',
        'url' => env('LOG_SLACK_WEBHOOK_URL'),
        'username' => 'Laravel Log',
        'emoji' => ':boom:',
        'level' => env('LOG_LEVEL', 'critical'),
    ],
],
```

### Health Checks

```bash
# Create health check endpoint
curl -f http://localhost/health || exit 1
```

## Backup & Recovery

### Database Backup

```bash
#!/bin/bash
# backup-script.sh

DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/var/backups/library-api"
DB_NAME="library_production"

# Create backup directory
mkdir -p $BACKUP_DIR

# MySQL backup
mysqldump -u library_user -p$DB_PASSWORD $DB_NAME > $BACKUP_DIR/library_$DATE.sql

# Compress backup
gzip $BACKUP_DIR/library_$DATE.sql

# Upload to S3
aws s3 cp $BACKUP_DIR/library_$DATE.sql.gz s3://your-backup-bucket/database/

# Clean up old backups (keep 7 days)
find $BACKUP_DIR -name "library_*.sql.gz" -mtime +7 -delete
```

### Application Backup

```bash
#!/bin/bash
# backup-app.sh

DATE=$(date +%Y%m%d_%H%M%S)
APP_DIR="/var/www/library-api"
BACKUP_DIR="/var/backups/library-api"

# Backup application files
tar -czf $BACKUP_DIR/app_$DATE.tar.gz \
    --exclude='vendor' \
    --exclude='node_modules' \
    --exclude='storage/logs' \
    $APP_DIR

# Upload to S3
aws s3 cp $BACKUP_DIR/app_$DATE.tar.gz s3://your-backup-bucket/application/
```

### Automated Backup Schedule

```cron
# Add to crontab
0 2 * * * /usr/local/bin/backup-script.sh
0 3 * * 0 /usr/local/bin/backup-app.sh
```

## CI/CD Pipeline

### GitHub Actions Workflow

`.github/workflows/deploy.yml`:
```yaml
name: Deploy to Production

on:
  push:
    branches: [main]

jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: 8.2
          
      - name: Install dependencies
        run: composer install --prefer-dist --no-progress
        
      - name: Run tests
        run: php artisan test

  deploy:
    needs: test
    runs-on: ubuntu-latest
    if: github.ref == 'refs/heads/main'
    
    steps:
      - uses: actions/checkout@v2
      
      - name: Deploy to server
        uses: appleboy/ssh-action@v0.1.5
        with:
          host: ${{ secrets.HOST }}
          username: ${{ secrets.USERNAME }}
          key: ${{ secrets.PRIVATE_KEY }}
          script: |
            cd /var/www/library-api
            git pull origin main
            composer install --no-dev --optimize-autoloader
            php artisan migrate --force
            php artisan config:cache
            php artisan route:cache
            php artisan queue:restart
            sudo systemctl reload nginx
```

### Zero-Downtime Deployment

```bash
#!/bin/bash
# deploy.sh

RELEASE_DIR="/var/www/releases/$(date +%Y%m%d_%H%M%S)"
CURRENT_DIR="/var/www/library-api"
REPO_URL="https://github.com/yourusername/library-api.git"

# Create release directory
mkdir -p $RELEASE_DIR

# Clone repository
git clone $REPO_URL $RELEASE_DIR

# Install dependencies
cd $RELEASE_DIR
composer install --no-dev --optimize-autoloader

# Copy environment file
cp $CURRENT_DIR/.env $RELEASE_DIR/.env

# Run migrations
php artisan migrate --force

# Cache configuration
php artisan config:cache
php artisan route:cache

# Update symlink
ln -nfs $RELEASE_DIR $CURRENT_DIR

# Reload services
sudo systemctl reload php8.2-fpm
sudo systemctl reload nginx

# Clean up old releases (keep 5)
cd /var/www/releases
ls -t | tail -n +6 | xargs rm -rf
```

## Troubleshooting

### Common Issues

1. **Permission Errors**
```bash
sudo chown -R www-data:www-data /var/www/library-api
sudo chmod -R 755 /var/www/library-api
sudo chmod -R 775 /var/www/library-api/storage
```

2. **Database Connection Issues**
```bash
# Test database connection
php artisan tinker
> DB::connection()->getPdo();
```

3. **Cache Issues**
```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

4. **Queue Issues**
```bash
# Restart queue workers
php artisan queue:restart
```

### Monitoring Commands

```bash
# Check application status
curl -I https://api.yourdomain.com/health

# Monitor logs
tail -f /var/www/library-api/storage/logs/laravel.log

# Check resource usage
htop
iotop
```

This deployment guide provides comprehensive coverage for deploying the Library Management System API in production environments with security, performance, and reliability best practices.