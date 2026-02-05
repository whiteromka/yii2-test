FROM composer:2.7 AS composer

FROM php:8.3-fpm

ARG UID
ARG GID

# Установка пакетов с повторными попытками
RUN apt-get update --fix-missing || apt-get update --fix-missing && \
    apt-get install -y \
    git unzip libpq-dev --no-install-recommends \
    && docker-php-ext-install pdo pdo_pgsql

# Composer из официального образа
COPY --from=composer /usr/bin/composer /usr/bin/composer

#RUN pecl install xdebug && docker-php-ext-enable xdebug
#RUN pecl install xdebug-3.2.2 && docker-php-ext-enable xdebug
# Установка XDebug с повторными попытками
RUN for i in $(seq 1 3); do \
        pecl install xdebug && break || sleep 5; \
    done && \
    docker-php-ext-enable xdebug

RUN groupadd -g ${GID} appuser \
    && useradd -u ${UID} -g appuser -m appuser

WORKDIR /var/www/html

COPY php.ini /usr/local/etc/php/conf.d/php.ini
COPY entrypoint.sh /usr/local/bin/entrypoint.sh

RUN chmod +x /usr/local/bin/entrypoint.sh

USER appuser

ENTRYPOINT ["entrypoint.sh"]
CMD ["php-fpm"]
