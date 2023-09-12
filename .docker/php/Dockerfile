FROM php:8.1-fpm

ARG TIMEZONE

MAINTAINER Aleksey <Supalk> Suprun <SuprunAK@edu.mos.ru>

# replace shell with bash so we can source files
RUN rm /bin/sh && ln -s /bin/bash /bin/sh

RUN apt-get update && apt-get install -y \
    openssl \
    git \
    curl \
    unzip \
    zlib1g-dev \
    libxml2-dev \
    libmcrypt-dev \
    apt-utils \
    libzip-dev

# PostgresSql
RUN apt-get update && \
    apt-get install -y libpq-dev && \
    docker-php-ext-install pdo pdo_pgsql pgsql

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Set timezone
RUN ln -snf /usr/share/zoneinfo/${TIMEZONE} /etc/localtime && echo ${TIMEZONE} > /etc/timezone
RUN printf '[PHP]\ndate.timezone = "%s"\n', ${TIMEZONE} > /usr/local/etc/php/conf.d/tzone.ini

#RUN usermod -u 1000 www-data

#USER www-data;

#WORKDIR /var/www/html

#RUN usermod -u UID www-data

#RUN usermod www-data -a -G phill
#RUN usermod -u 1000 www-data


RUN chown -R www-data:www-data /var/www/html
