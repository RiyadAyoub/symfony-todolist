#!/usr/bin/env bash
# Arrêter le script en cas d'erreur
set -o errexit

composer install --no-dev --optimize-autoloader
php bin/console doctrine:migrations:migrate --no-interaction