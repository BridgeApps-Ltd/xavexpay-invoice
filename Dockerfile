# --- Build Stage 1: Composer & PHP dependencies ---
FROM php:8.1-fpm-alpine as composer

# Install system dependencies
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libxml2-dev \
    zip \
    unzip \
    sqlite-dev \
    libzip-dev

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql pdo_sqlite bcmath gd zip

# Get latest Composer
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Copy the entire project including vendor directory
COPY . .

# --- Build Stage 2: Node/Yarn/Vite ---
FROM php:8.1-fpm-alpine as node

# Install system dependencies and Node.js
RUN apk add --no-cache \
    nodejs \
    yarn \
    libpng-dev \
    libxml2-dev \
    sqlite-dev \
    libzip-dev

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql pdo_sqlite bcmath gd

WORKDIR /var/www

# Copy the entire project including node_modules
COPY . .

# Set permissions for build (excluding node_modules)
RUN chown -R $user:www-data /var/www && chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Build assets using local node_modules
RUN yarn build

# --- Build Stage 3: Production ---
FROM php:8.1-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    libpng-dev \
    libxml2-dev \
    sqlite-dev \
    libzip-dev

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql pdo_sqlite bcmath gd

WORKDIR /var/www

# Copy built assets from node stage
COPY --from=node /var/www/public/build ./public/build

# Copy application files from composer stage
COPY --from=composer /var/www .

# Copy .env.stub to .env for SQLite usage
RUN cp .env.stub .env

# Set permissions (adjust as needed)
RUN chown -R $user:www-data /var/www && chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Expose port for php server
EXPOSE 8000

# Start Laravel development server
CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]