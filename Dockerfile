FROM php:8.3-apache

# Installe les extensions PHP nécessaires (ajoutez pgsql)
RUN apt-get update && apt-get install -y \
    libzip-dev \
    unzip \
    libpq-dev \
    && docker-php-ext-install pdo pdo_mysql pdo_pgsql zip \
    && rm -rf /var/lib/apt/lists/*

# Active mod_rewrite
RUN a2enmod rewrite

# Configure Apache pour pointer vers public/
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Installe Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copie le code source
COPY . /var/www/html/
WORKDIR /var/www/html

# Installe les dépendances PHP avec toutes les dépendances d'abord
RUN composer install --optimize-autoloader --no-interaction


# Permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 775 var/

EXPOSE 80
CMD ["apache2-foreground"]