FROM php:8.4-apache

# Устанавливаем системные зависимости
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpq-dev \
    zip \
    unzip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Устанавливаем PHP-расширения
RUN docker-php-ext-install pdo_pgsql pgsql

# Включаем mod_rewrite для Apache
RUN a2enmod rewrite

# Устанавливаем Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Устанавливаем рабочую директорию
WORKDIR /var/www/html

# Копируем файлы приложения
COPY . .

# Разрешаем git в этой директории
RUN git config --global --add safe.directory /var/www/html

# Устанавливаем зависимости Composer
RUN composer install --no-interaction --optimize-autoloader --no-dev

# Настраиваем Apache
RUN echo "<VirtualHost *:80>\n\
    DocumentRoot /var/www/html/public\n\
    <Directory /var/www/html/public>\n\
        Options Indexes FollowSymLinks\n\
        AllowOverride All\n\
        Require all granted\n\
    </Directory>\n\
</VirtualHost>" > /etc/apache2/sites-available/000-default.conf

# Права на storage
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Генерируем application key
RUN php artisan key:generate

EXPOSE 80

CMD ["apache2-foreground"]
