FROM php:7.2-apache
# Указываем рабочую папку
WORKDIR /var/www
# Копируем все файлы проекта в контейнер
RUN apt-get update && apt-get install -y \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libicu-dev \
        wget \
        git \
        software-properties-common \
        libpq-dev \
        libonig-dev \
            --no-install-recommends

RUN apt-get install -y \
        libzip-dev \
        zip

RUN docker-php-ext-install \
    zip \
    intl \
    mbstring \
    pdo_mysql

COPY ./php.ini /usr/local/etc/php/

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

EXPOSE 9000
#CMD ["php-fpm"]