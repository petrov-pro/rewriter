FROM php:8.2-fpm
ARG NEW_RELIC_KEY
ENV NEW_RELIC_KEY=${NEW_RELIC_KEY}

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
RUN crontab /etc/cron.d/main-crontab

COPY ./supervisor/messenger-worker.conf /etc/supervisor/supervisord.conf


RUN \
  curl -L https://download.newrelic.com/php_agent/release/newrelic-php5-10.9.0.324-linux.tar.gz | tar -C /tmp -zx && \
  export NR_INSTALL_USE_CP_NOT_LN=1 && \
  export NR_INSTALL_SILENT=1 && \
  /tmp/newrelic-php5-*/newrelic-install install && \
  rm -rf /tmp/newrelic-php5-* /tmp/nrinstall* && \
  sed -i \
      -e "s/REPLACE_WITH_REAL_KEY/${NEW_RELIC_KEY}/" \
      -e 's/newrelic.appname = "PHP Application"/newrelic.appname = "Rewriter"/' \
      -e 's/;newrelic.daemon.app_connect_timeout =.*/newrelic.daemon.app_connect_timeout=15s/' \
      -e 's/;newrelic.daemon.start_timeout =.*/newrelic.daemon.start_timeout=5s/' \
      -e 's/;newrelic.application_logging.enabled = true/newrelic.application_logging.enabled = true/' \
      -e 's/;newrelic.application_logging.metrics.enabled = true/newrelic.application_logging.metrics.enabled = true/' \
      -e 's/;newrelic.application_logging.forwarding.enabled = true/newrelic.application_logging.forwarding.enabled = true/' \
      -e 's/;newrelic.application_logging.forwarding.log_level = "WARNING"/newrelic.application_logging.forwarding.log_level = "DEBUG"/' \
      /usr/local/etc/php/conf.d/newrelic.ini

COPY ./app /var/www/html

# Назначаем рабочую директорию и пользователя www-data
WORKDIR /var/www/html

RUN composer clearcache \
    && composer install \
    && composer dumpautoload -o

RUN chown -R www-data:www-data /var/www/html
RUN chown -R www-data:www-data /var/www && chmod -R 775 /var/www

USER www-data

CMD ["/usr/bin/supervisord"]