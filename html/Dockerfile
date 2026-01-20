FROM php:8.2-apache

# Įdiegiame PostgreSQL tvarkykles
RUN apt-get update && apt-get install -y libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql

# Nukopijuojame viską iš tavo aplankalo į serverį
# Jei tavo aplankalas vadinasi, pvz., 'mano_svetaine', rašyk: COPY mano_svetaine/ /var/www/html/
# Jei nori nukopijuoti viską, kas yra saugykloje:
COPY . /var/www/html/

RUN a2enmod rewrite
EXPOSE 80
