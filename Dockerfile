# Laravel 13 / Symfony récents : ReflectionProperty::isVirtual() requiert PHP 8.4+
FROM php:8.4-fpm-alpine

# Paquets *-dev pour compiler les extensions ; ne pas les purger sans réinstaller les libs runtime (sinon gd/intl/zip cassent au chargement).
RUN apk add --no-cache \
    libzip-dev \
    icu-dev \
    icu-data-full \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    oniguruma-dev \
    libxml2-dev

RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        mbstring \
        zip \
        intl \
        opcache \
        gd \
        exif \
        pcntl \
        dom \
        xml

# Retirer les en-têtes de build, garder les .so runtime
RUN apk del --purge \
    libzip-dev \
    icu-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    oniguruma-dev \
    libxml2-dev \
    && apk add --no-cache \
        libzip \
        icu-libs \
        icu-data-full \
        libpng \
        libjpeg-turbo \
        freetype \
        oniguruma \
        libxml2

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
