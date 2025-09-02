FROM php:8.2-fpm

# Установка mysqli и других полезных расширений
RUN docker-php-ext-install mysqli pdo pdo_mysql

RUN pecl install xdebug \
    && docker-php-ext-enable xdebug