FROM yavin/alpine-php-fpm:7.0

RUN apk add --no-cache php7-pdo_pgsql

COPY src /app/

RUN wget https://getcomposer.org/composer.phar \
    && php composer.phar -d=/app install --no-interaction \
    && rm composer.phar

RUN echo "clear_env = no" >> /etc/php7/php-fpm.conf
