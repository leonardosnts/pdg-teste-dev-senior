FROM php:8.2-cli

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    default-mysql-client \
    && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-install zip pdo pdo_mysql

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www

COPY composer.json composer.lock* ./
ENV COMPOSER_MEMORY_LIMIT=-1
RUN composer install --no-scripts --no-autoloader --prefer-dist

COPY . .

RUN mkdir -p storage/logs storage/framework/sessions storage/framework/views storage/framework/cache storage/framework/cache/data storage/app/public bootstrap/cache

RUN chown -R www-data:www-data storage bootstrap/cache && \
    chmod -R 775 storage bootstrap/cache

RUN echo '#!/bin/bash\n\
    echo "Aguardando MySQL estar pronto..."\n\
    until mysql -hmysql -uroot -proot --skip-ssl -e "SELECT 1" >/dev/null 2>&1; do\n\
    echo "MySQL ainda não está pronto - aguardando..."\n\
    sleep 2\n\
    done\n\
    echo "MySQL está pronto!"\n\
    \n\
    if [ ! -f .env ]; then\n\
    cp .env.example .env\n\
    fi\n\
    \n\
    echo "Executando migrations..."\n\
    php artisan migrate:fresh --seed --force\n\
    \n\
    echo "Iniciando queue worker em background..."\n\
    php artisan queue:work --sleep=3 --tries=3 --max-jobs=1000 &\n\
    \n\
    echo "Iniciando servidor Laravel..."\n\
    exec php -d max_execution_time=300 artisan serve --host=0.0.0.0 --port=8090' > /usr/local/bin/entrypoint.sh && chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 8090

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]