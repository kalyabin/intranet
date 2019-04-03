FROM php:7.2-fpm

RUN mkdir -p /usr/share/man/man1 && \
    mkdir -p /usr/share/man/man7 && \
    apt-get update && apt-get install -y \
    postgresql-client \
    libmcrypt4 \
    libmcrypt-dev \
    libreadline-dev \
    libcurl3-dev \
    libicu-dev \
    libpq-dev \
    libzip-dev \
    zlib1g-dev \
    git && \
    pecl install mcrypt-1.0.1 && \
    docker-php-ext-enable mcrypt

RUN docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql
RUN docker-php-ext-install zip curl json pdo pdo_pgsql pgsql intl mbstring

RUN touch /usr/local/etc/php/conf.d/custom.ini && \
    echo "memory_limit = -1" >> /usr/local/etc/php/conf.d/custom.ini

ENV PATH "/composer/vendor/bin:/composer/home/vendor/bin:$PATH"
ENV COMPOSER_ALLOW_SUPERUSER 1
ENV COMPOSER_HOME /composer/home

RUN curl -sS https://getcomposer.org/installer | php -- \
      --install-dir=/usr/local/bin \
      --filename=composer

EXPOSE 8001

CMD cd /backend && \
    cp app/config/parameters.yml.docker app/config/parameters.yml && \
    /bin/bash /configure-parameters.sh && \
    /bin/bash /wait-for-postgres.sh && \
    composer install --no-interaction && \
    ./bin/symfony_requirements && \
    ./bin/console doctrine:migrations:migrate --env=test && \
    ./vendor/bin/phpunit --verbose && \
    ./bin/console doctrine:migrations:migrate --env=dev && \
    ./bin/console server:run *:8000
