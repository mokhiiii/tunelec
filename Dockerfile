FROM php:8.2-apache

# Install PHP extensions needed by this project
RUN apt-get update \
    ; apt-get install -y --no-install-recommends \
        git \
        unzip \
        libzip-dev \
        libpng-dev \
        libonig-dev \
        libxml2-dev \
        zip \
    ; docker-php-ext-install pdo pdo_mysql zip mbstring gd xml \
    ; rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Enable Apache rewrite module
RUN a2enmod rewrite

# Set document root to project root
ENV APACHE_DOCUMENT_ROOT=/var/www/html
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    ; sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Copy app files
COPY . /var/www/html

# Install PHP dependencies from composer.json
RUN cd /var/www/html \
    ; composer install --no-dev --optimize-autoloader --no-interaction

# Set permissions
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
