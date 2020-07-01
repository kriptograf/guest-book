FROM php:7.4-fpm

# Install modules
RUN apt-get update && apt-get install -y --no-install-recommends --no-install-suggests \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libicu-dev \
        libpq-dev \
        librabbitmq-dev \
        wget \
        jpegoptim \
        optipng \
        pngquant \
        gifsicle \
        git \
        libzip-dev \
        zip \
        libonig-dev

RUN docker-php-ext-install bcmath zip intl pdo_pgsql exif \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd \
    && docker-php-ext-install zip

#install Imagemagick & PHP Imagick ext
RUN apt-get update && apt-get install -y \
        libmagickwand-dev --no-install-recommends

#RUN pecl install imagick && docker-php-ext-enable imagick

# install apcu
RUN pecl install apcu \
	&& docker-php-ext-enable apcu

RUN pecl install -o -f xdebug \
    && rm -rf /tmp/pear

RUN pecl install amqp \
	&& docker-php-ext-enable amqp