FROM php:8.2-apache

# Install MariaDB and required packages
RUN apt-get update && apt-get install -y --no-install-recommends \
    mariadb-server \
    mariadb-client \
    && rm -rf /var/lib/apt/lists/*

# Install PDO MySQL extension
RUN docker-php-ext-install pdo pdo_mysql

# Enable Apache rewrite module
RUN a2enmod rewrite

# Enable .htaccess overrides
RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . /var/www/html/

# Copy and prepare startup script
RUN chmod +x /var/www/html/start.sh \
    && cp /var/www/html/start.sh /usr/local/bin/start-app.sh

EXPOSE 80

CMD ["/usr/local/bin/start-app.sh"]
