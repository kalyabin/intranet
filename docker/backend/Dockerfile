FROM php:7.1-fpm

RUN apt-get update && apt-get install -y \
    libmcrypt4 \
    libmcrypt-dev \
    libreadline-dev \
    libcurl3-dev \
    libicu-dev \
    libpq-dev \
    git

RUN docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql
RUN docker-php-ext-install mcrypt zip curl json pdo pdo_pgsql pgsql intl mbstring

ENV PATH "/composer/vendor/bin:/composer/home/vendor/bin:$PATH"
ENV COMPOSER_ALLOW_SUPERUSER 1
ENV COMPOSER_HOME /composer/home

RUN curl -sS https://getcomposer.org/installer | php -- \
      --install-dir=/usr/local/bin \
      --filename=composer

EXPOSE 8001

CMD cd /backend && \
    cp app/config/parameters.yml.docker app/config/parameters.yml && \
    composer install --no-interaction && \
    ./bin/symfony_requirements && \
    ./bin/console doctrine:migrations:migrate --env=dev && \
    ./bin/console server:run
