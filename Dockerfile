# On utilise PHP 8.2 avec Apache
FROM php:8.2-apache

# 1. On installe les bibliothèques système nécessaires (zip, intl, postgres)
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    libicu-dev \
    zip \
    unzip \
    && docker-php-ext-install pdo pdo_pgsql zip intl opcache

# 2. On active la réécriture d'URL pour Symfony
RUN a2enmod rewrite
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# 3. On copie le code dans le container
COPY . /var/www/html

# 4. On installe Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 5. On définit les variables pour que Composer ne bloque pas
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV APP_ENV=prod

# 6. Installation des dépendances sans interaction
RUN composer install --no-dev --optimize-autoloader --no-interaction

# 7. On donne les droits au serveur web sur le cache
RUN chown -R www-data:www-data /var/www/html/var