FROM composer:1.9 as composer
COPY composer.* /app/
RUN composer install

FROM php:7.4.6-apache

RUN apt-get update

RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"
RUN sed -i 's/variables_order = "GPCS"/variables_order = "EGPCS"/' "$PHP_INI_DIR/php.ini"

COPY --from=composer /app/vendor /var/www/html/vendor
COPY guc-ical.php /var/www/html/index.php

RUN chown www-data:www-data -R /var/www/html