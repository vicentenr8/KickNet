ARG SYMFONY_PARAMS="MockApp"

FROM php:8.2-apache
#RUN apt update && apt install -y zip git curl && git config --global user.email "you@example.com" && git config --global user.name "Your Name"
RUN apt update && apt install -y \
    libicu-dev \
    libonig-dev \
    libzip-dev \
    zip \
    unzip \
    curl \
    git \
    libpq-dev \
    && git config --global user.email "you@example.com" && git config --global user.name "Your Name" \
    && docker-php-ext-configure intl \
    && docker-php-ext-install intl \
    && docker-php-ext-install mbstring \
    && docker-php-ext-install zip \
    && docker-php-ext-install pdo_pgsql
RUN docker-php-ext-enable intl mbstring zip pdo_pgsql


COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV SYMFONY_PARAMS_STD="MockApp"

RUN curl -1sLf 'https://dl.cloudsmith.io/public/symfony/stable/setup.deb.sh' | bash && apt install -y symfony-cli

WORKDIR /usr/src
VOLUME /usr/src

COPY ./docker/php/docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

ENTRYPOINT ["docker-entrypoint.sh"]

CMD sh -c " echo 'Installing dependencies...';composer install;echo 'Waiting for PostgreSQL to be ready...';until php bin/console doctrine:query:sql 'SELECT 1' > /dev/null 2>&1; do echo 'Database not ready. Retrying in 5 seconds...'; sleep 5; done; echo 'Creating database if it does not exist...';php bin/console doctrine:database:create --if-not-exists;echo 'Running migrations...';php bin/console doctrine:migrations:migrate --no-interaction;echo 'Starting Apache...';apache2-foreground"


