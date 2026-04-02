FROM php:8.2-apache

# Install mysqli extension
RUN docker-php-ext-install mysqli

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Enable Prometheus Reporting
RUN a2enmod status
COPY apache-status.conf /etc/apache2/conf-enabled/apache-status.conf

# Copy source files into container
COPY . /var/www.html/
