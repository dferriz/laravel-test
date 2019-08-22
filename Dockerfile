FROM php:7.2-fpm-alpine

RUN apk update && apk add --no-cache \
    mysql-client \
    libpng-dev \
    jpegoptim optipng pngquant gifsicle \
    vim \
    unzip \
    git \
    curl


RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && rm -rf /tmp/*

COPY . /usr/src/app

WORKDIR /usr/src/app

RUN composer install --no-progress --no-suggest --no-interaction
#    && yarn install \
#    && yarn encore dev
