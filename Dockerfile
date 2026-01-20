FROM php:8.2-apache

# Įdiegiame PostgreSQL bibliotekas ir PHP plėtinius
RUN apt-get update && apt-get install -y libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql

# Nukopijuojame failus
COPY . /var/www/html/

RUN a2enmod rewrite
EXPOSE 80
