# Composer depuis l'image officielle (binaire seulement)
FROM composer:2 AS composer

# Image PHP officielle
FROM php:8.3-cli

# Outils nécessaires à Composer (git pour les dépendances en source, unzip
# pour les archives — l'extension zip n'est pas livrée par défaut)
RUN apt-get update \
    && apt-get install -y --no-install-recommends git unzip \
    && rm -rf /var/lib/apt/lists/*

# Composer depuis l'image officielle
COPY --from=composer /usr/bin/composer /usr/local/bin/composer

WORKDIR /app

# Note : pdo_sqlite et mbstring sont inclus par défaut dans php:8.3-cli
