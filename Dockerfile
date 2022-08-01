FROM icewind1991/php-alpine-apcu

RUN wget -q https://github.com/demostf/parser/releases/download/v0.4.0/parse_demo -O /app/parse_demo && \
    chmod +x /app/parse_demo
COPY composer.json composer.lock /app/

RUN apk add --no-cache git \
    && wget -q https://getcomposer.org/composer.phar \
    && php composer.phar --working-dir=/app install --no-dev --no-interaction --ignore-platform-reqs \
    && rm composer.phar \
    && apk del git

COPY src /app/src

ENV PARSER_PATH /app/parse_demo

COPY php-fpm.conf /usr/local/etc/php/php-fpm.conf

RUN echo "post_max_size = 150M" >> /usr/local/etc/php/php.ini \
    && echo "upload_max_filesize = 150M" >> /usr/local/etc/php/php.ini
