FROM php:8.0-fpm

RUN apt-get update && apt-get install -y \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libicu-dev \
        wget \
        git \
        postgresql \
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
    pdo_mysql \
    exif bcmath \
    calendar \
    pdo_pgsql \
    pgsql \

    && docker-php-ext-configure gd --with-freetype=/usr/include/ --with-jpeg=/usr/include/ \
    && docker-php-ext-install gd

RUN pecl install -o -f xdebug \
    mongodb \
    && docker-php-ext-enable mongodb \
    && rm -rf /tmp/pear

COPY ./php.ini /usr/local/etc/php/
COPY ./www.conf /usr/local/etc/php/

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN apt-get purge -y g++ \
    && apt-get autoremove -y \
    && rm -r /var/lib/apt/lists/* \
    && rm -rf /tmp/*
#    && sh /install-composer.sh \
#    && rm /install-composer.sh

RUN usermod -u 1000 www-data

VOLUME /root/.composer
WORKDIR /app

EXPOSE 9000
CMD ["php-fpm"]
