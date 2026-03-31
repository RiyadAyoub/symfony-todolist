FROM php:8.2-apache

# 1. Installation des extensions nécessaires
RUN apt-get update && apt-get install -y \
    libpq-dev libzip-dev libicu-dev zip unzip \
    && docker-php-ext-install pdo pdo_pgsql zip intl opcache

# 2. Configuration Apache
RUN a2enmod rewrite
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# 3. ON DEFINIT LE DOSSIER DE TRAVAIL (C'est ça qui manquait !)
WORKDIR /var/www/html

# 4. Copie du projet
COPY . .

# 5. Installation de Composer et des dépendances
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV APP_ENV=prod
RUN composer install --no-dev --optimize-autoloader --no-interaction

# 6. Droits d'accès
RUN chown -R www-data:www-data /var/www/html/var

# 7. COMMANDE FINALE : Migration + Lancement du serveur
CMD php bin/console doctrine:migrations:migrate --no-interaction && apache2-foreground