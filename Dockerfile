# Dockerfile
FROM php:8.3-cli

# Install system dependencies and Composer
RUN apt-get update && apt-get install -y \
    git unzip zip libzip-dev && \
    docker-php-ext-install zip && \
    rm -rf /var/lib/apt/lists/*

# Install Composer globally
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Configure git to trust the /app directory (fixes ownership warnings)
RUN git config --global --add safe.directory /app

# Set working directory
WORKDIR /app

# Copy project files
COPY . .

# Install dependencies
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Default command (run tests)
CMD ["tail", "-f", "/dev/null"]