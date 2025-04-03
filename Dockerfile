# Use the latest PHP image
FROM php:latest-cli

# Install necessary extensions
RUN docker-php-ext-install opcache

# Set the working directory
WORKDIR /app

# Copy project files
COPY . .

# Install dependencies
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer 
RUN composer install --no-dev --prefer-dist --no-progress --no-suggest

# Default command
CMD ["php", "-a"]