# SIZSR — PHP 8 + Apache for Render (or any Docker host)
FROM php:8.2-apache

RUN apt-get update && apt-get install -y --no-install-recommends \
    libzip-dev \
    unzip \
    && docker-php-ext-install pdo pdo_mysql \
    && a2enmod rewrite headers deflate expires \
    && sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

COPY . /var/www/html/

# Production: site runs at domain root (not /Somaliland-innovation/)
RUN sed -i 's|RewriteBase /Somaliland-innovation/|RewriteBase /|g' /var/www/html/.htaccess

RUN mkdir -p assets/uploads/courses assets/uploads/documents assets/uploads/media \
    && chown -R www-data:www-data assets/uploads

ENV PORT=80
EXPOSE 80

CMD ["apache2-foreground"]
