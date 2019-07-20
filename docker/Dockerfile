FROM php:7.3-alpine

ARG UID

# Create a user account manually rather than using:
# adduser -D -u $UID -h /home/dev dev
# This is necessary to support $UIDs > 256000.
RUN echo "dev:x:$UID:$UID::/home/dev:" >> /etc/passwd \
    && echo "dev:!:$(($(date +%s) / 60 / 60 / 24)):0:99999:7:::" >> /etc/shadow \
    && echo "dev:x:$UID:" >> /etc/group \
    && mkdir /home/dev && chown dev: /home/dev

RUN apk add --no-cache git openssh-client openssh g++ make autoconf icu-dev

RUN docker-php-source extract \
    && { \
        echo 'xdebug.remote_enable=1'; \
        echo 'xdebug.remote_autostart=1'; \
        echo 'xdebug.remote_connect_back=1'; \
        echo 'xdebug.max_nesting_level=1200'; \
        echo 'xdebug.idekey = PHPSTORM'; \
    } > /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && docker-php-ext-install -j$(getconf _NPROCESSORS_ONLN) intl \
    && docker-php-source delete \
    && rm -rf /tmp/*

# Set timezone
RUN echo "UTC" > /etc/timezone

# Install composer globally
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin/ --filename=composer

COPY php.ini /usr/local/etc/php/php.ini

USER dev

# Update PATH to include project binaries
ENV PATH="/src/vendor/bin:${PATH}"

WORKDIR /src