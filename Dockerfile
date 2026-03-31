# On part d'une image PHP officielle avec Apache
FROM php:8.2-apache

# On installe les outils pour PostgreSQL (car Render l'utilise en version gratuite)
RUN apt-get update && apt-get install -y libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql

# On active le module Rewrite d'Apache pour Symfony
RUN a2enmod rewrite

# On dit à Apache que le dossier public est 'public/'
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# On copie tout ton code dans le serveur
COPY . /var/www/html

# On installe Composer pour installer les dépendances sur Render
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --optimize-autoloader

# On donne les droits d'écriture pour le cache de Symfony
RUN chown -R www-data:www-data /var/www/html/var