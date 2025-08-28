FROM php:8.2-fpm

# Set working directory
WORKDIR /var/www

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libwebp-dev \
    libxpm-dev \
    libgd-dev \
    libmagickwand-dev \
    imagemagick \
    libicu-dev \
    libsodium-dev \
    && rm -rf /var/lib/apt/lists/*

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions required by Laravel
RUN docker-php-ext-configure gd \
    --with-freetype \
    --with-jpeg \
    --with-webp \
    --with-xpm

# Install extensions that don't need special configuration first
RUN docker-php-ext-install \
    pdo_mysql \
    bcmath \
    exif \
    pcntl \
    gd \
    zip \
    intl \
    soap

# Note: Some extensions are built-in to PHP 8.2 and don't need installation:
# - mbstring (built-in since PHP 7.4)
# - dom, xml, xmlreader, xmlwriter, simplexml (built-in)
# - json (built-in since PHP 8.0)
# - tokenizer, ctype, filter, hash, session, fileinfo (built-in)

# Install Imagick extension
RUN pecl install imagick && docker-php-ext-enable imagick

# Install Redis extension
RUN pecl install redis && docker-php-ext-enable redis

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Create system user to run Composer and Artisan Commands
RUN groupadd -g 1000 www
RUN useradd -u 1000 -ms /bin/bash -g www www

# Copy existing application directory contents
COPY . /var/www

# Set proper permissions
RUN chown -R www-data:www-data /var/www
RUN chmod -R 775 /var/www/storage
RUN chmod -R 775 /var/www/bootstrap/cache

# Change current user to www
USER www

# Expose port 9000 and start php-fpm server
EXPOSE 9000
CMD ["php-fpm"]
