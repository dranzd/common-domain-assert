# Development image: PHP CLI with Composer and a coverage driver.
# The project is bind-mounted at /app (see compose.yaml); nothing is baked in.
FROM php:8.3-cli

RUN apt-get update \
    && apt-get install -y --no-install-recommends git unzip \
    && rm -rf /var/lib/apt/lists/* \
    && pecl install pcov \
    && docker-php-ext-enable pcov \
    && git config --system --add safe.directory /app

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

CMD ["composer", "check"]
