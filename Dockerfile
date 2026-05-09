FROM php:8.2-apache

# Instala as dependências do sistema e extensões do PHP para o CI4 e Postgres
RUN apt-get update && apt-get install -y \
    libicu-dev \
    libzip-dev \
    libpq-dev \
    zip \
    unzip \
    && docker-php-ext-configure intl \
    && docker-php-ext-install intl pdo pdo_pgsql pgsql zip

# Habilita o mod_rewrite do Apache (vital para as rotas do CodeIgniter)
RUN a2enmod rewrite

# Aponta a raiz do servidor web direto para a pasta public do CI4
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf