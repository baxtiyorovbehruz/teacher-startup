#!/bin/bash
set -e

PORT="${PORT:-80}"
DB_HOST="${DB_HOST:-127.0.0.1}"

# If DB_HOST is localhost or 127.0.0.1, run internal MariaDB
if [ "$DB_HOST" = "127.0.0.1" ] || [ "$DB_HOST" = "localhost" ]; then
    echo "=== Starting local MariaDB ==="
    mkdir -p /var/run/mysqld /var/lib/mysql
    chown -R mysql:mysql /var/run/mysqld /var/lib/mysql

    if [ ! -d "/var/lib/mysql/mysql" ]; then
        echo "Installing MariaDB system tables..."
        mysql_install_db --user=mysql --datadir=/var/lib/mysql > /dev/null 2>&1 || true
    fi

    service mariadb start || /etc/init.d/mariadb start

    for i in {1..30}; do
        if mysqladmin ping -h localhost --silent; then
            break
        fi
        sleep 1
    done

    if ! mysql -e "USE tadbirkor;" 2>/dev/null; then
        echo "Creating tadbirkor database..."
        mysql -e "CREATE DATABASE IF NOT EXISTS tadbirkor CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
        mysql -e "CREATE USER IF NOT EXISTS 'tadbirkor'@'localhost' IDENTIFIED BY 'tadbirkor_pass';"
        mysql -e "GRANT ALL PRIVILEGES ON tadbirkor.* TO 'tadbirkor'@'localhost'; FLUSH PRIVILEGES;"

        if [ -f "/var/www/html/database/schema.sql" ]; then
            echo "Importing schema.sql..."
            mysql tadbirkor < /var/www/html/database/schema.sql
        fi

        if [ -f "/var/www/html/database/seed.sql" ]; then
            echo "Importing seed.sql..."
            mysql tadbirkor < /var/www/html/database/seed.sql
        fi
        echo "Database setup completed!"
    fi

    export DB_HOST="127.0.0.1"
    export DB_PORT="3306"
    export DB_NAME="tadbirkor"
    export DB_USER="tadbirkor"
    export DB_PASSWORD="tadbirkor_pass"
fi

# Pass environment variables to Apache
echo "export DB_HOST='$DB_HOST'" >> /etc/apache2/envvars
echo "export DB_PORT='${DB_PORT:-3306}'" >> /etc/apache2/envvars
echo "export DB_NAME='${DB_NAME:-tadbirkor}'" >> /etc/apache2/envvars
echo "export DB_USER='${DB_USER:-tadbirkor}'" >> /etc/apache2/envvars
echo "export DB_PASSWORD='${DB_PASSWORD:-tadbirkor_pass}'" >> /etc/apache2/envvars
echo "export APP_URL='${APP_URL:-}'" >> /etc/apache2/envvars

# Configure Apache port
sed -i "s/Listen [0-9]*/Listen $PORT/g" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:[0-9]*>/<VirtualHost \*:$PORT>/g" /etc/apache2/sites-available/000-default.conf

# Permissions for storage directory
chown -R www-data:www-data /var/www/html/storage /var/www/html/assets/uploads 2>/dev/null || true
chmod -R 775 /var/www/html/storage /var/www/html/assets/uploads 2>/dev/null || true

echo "=== Starting Apache on port $PORT ==="
exec apache2-foreground
