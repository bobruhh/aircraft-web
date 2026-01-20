# Naudojame oficialų PHP su Apache serveriu
FROM php:8.2-apache

# Įdiegiame PostgreSQL palaikymą PHP kalbai
RUN apt-get update && apt-get install -y libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql

# Nukopijuojame visus tavo failus į serverio aplanką
COPY . /var/www/html/

# Leidžiame Apache perrašyti URL (jei naudosi .htaccess)
RUN a2enmod rewrite

# Atidarome 80 prievadą
EXPOSE 80