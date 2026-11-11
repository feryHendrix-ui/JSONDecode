FROM dunglas/frankenphp:php8.3
RUN install-php-extensions pdo_pgsql pdo_sqlite intl zip
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
WORKDIR /app
COPY . /app
RUN composer install --no-dev --prefer-dist --no-interaction --no-progress --optimize-autoloader \
    && mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs \
    && chmod -R ug+rw storage bootstrap/cache
COPY Caddyfile /etc/caddy/Caddyfile
RUN chmod +x /app/docker-entrypoint.sh
ENV APP_ENV=production APP_DEBUG=false LOG_CHANNEL=stderr
EXPOSE 8080
ENTRYPOINT ["/app/docker-entrypoint.sh"]
