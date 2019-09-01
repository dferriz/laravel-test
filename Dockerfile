FROM php:7.2-fpm-alpine

RUN apk update \
    && apk add --no-cache \
        git \
        curl \
        mysql-client \
        libpng-dev \
        jpegoptim optipng pngquant gifsicle \
        unzip \
        # needed for php ext
        libxml2-dev \
        icu-dev \
        g++ \
        make \
        autoconf \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && docker-php-ext-install pdo_mysql soap intl zip sysvsem


RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && rm -rf /tmp/*

COPY --chown=www-data:www-data . /usr/src/app


WORKDIR /usr/src/app

RUN composer install --no-progress --no-suggest --no-interaction
#    && yarn install \
#    && yarn encore dev
