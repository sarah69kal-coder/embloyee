# 1. Use the official PHP 8.2 with Apache (Web Server)
FROM php:8.2-apache

# 2. Install the PostgreSQL drivers (Crucial for your Aiven DB!)
RUN apt-get update && apt-get install -y libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql

# 3. Copy your project files into the web server folder
COPY . /var/www/html/

# 4. Tell Render to listen on Port 80
EXPOSE 80