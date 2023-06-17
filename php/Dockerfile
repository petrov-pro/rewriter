FROM php:8.2-fpm


# Обновляем и устанавливаем необходимые пакеты
RUN apt-get update && apt-get install -y \
    git \
    libzip-dev \
    libicu-dev \
    libonig-dev \
    librabbitmq-dev \
    unzip \
    wget \
    cron \
    procps \
    nano \
    && docker-php-ext-configure intl \
    && docker-php-ext-install intl zip

# Install Postgre PDO
RUN apt-get install -y libpq-dev \
    && docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
    && docker-php-ext-install pdo pdo_pgsql pgsql

RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

RUN pecl install amqp && docker-php-ext-enable amqp

# Install Redis extension
RUN pecl install redis && docker-php-ext-enable redis


# Устанавливаем Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Устанавливаем Symfony CLI
RUN wget https://github.com/symfony/cli/releases/download/v4.25.6/symfony_linux_amd64.gz && \
    gzip -d symfony_linux_amd64.gz && \
    mv symfony_linux_amd64 /usr/local/bin/symfony && \
    chmod +x /usr/local/bin/symfony

RUN apt-get install -y supervisor
RUN mkdir -p /var/log/supervisor

COPY ./cron/main-crontab /etc/cron.d/main-crontab
RUN chmod 0644 /etc/cron.d/main-crontab
#RUN crontab /etc/cron.d/main-crontab
RUN touch /var/log/cron.log

COPY ./supervisor/messenger-worker.conf /etc/supervisor/supervisord.conf

COPY ./app /var/www/html

# Назначаем рабочую директорию и пользователя www-data
WORKDIR /var/www/html

RUN composer clearcache \
    && composer install \
    && composer dumpautoload -o

RUN chown -R www-data:www-data /var/www/html
RUN chown -R www-data:www-data /var/www && chmod -R 775 /var/www

CMD ["/usr/bin/supervisord"]