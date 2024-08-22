# Default to PHP 8.2, but we attempt to match
# the PHP version from the user (wherever `flyctl launch` is run)
# Valid version values are PHP 7.4+
ARG PHP_VERSION=8.3
ARG NODE_VERSION=18
FROM fideloper/fly-laravel:${PHP_VERSION} as base

# PHP_VERSION needs to be repeated here
# See https://docs.docker.com/engine/reference/builder/#understand-how-arg-and-from-interact
ARG PHP_VERSION

LABEL fly_launch_runtime="laravel"

# Install PHP extensions
RUN apt update && apt install -y \
    php-mongodb php-redis php-mbstring php-zip php-xml

# Copy application code, skipping files based on .dockerignore
COPY . /var/www/html
COPY .env.production /var/www/html/.env

# Install Composer dependencies
RUN --mount=type=cache,target=/root/.composer/cache composer install --no-dev

EXPOSE 8080

ENTRYPOINT ["php", "-S", "0.0.0.0:8080", "-t", "/var/www/html/public"]
