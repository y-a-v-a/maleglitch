# Development image for Maleglitch: PHP 8.3 + Apache with GD (JPEG support).
# The project source is not copied in; it is bind-mounted by compose.yaml.
FROM php:8.3-apache

RUN apt-get update \
	&& apt-get install -y --no-install-recommends \
		libfreetype6-dev \
		libjpeg62-turbo-dev \
		libpng-dev \
	&& docker-php-ext-configure gd --with-freetype --with-jpeg \
	&& docker-php-ext-install -j"$(nproc)" gd \
	&& rm -rf /var/lib/apt/lists/*

# Development defaults (display_errors, E_ALL) plus project overrides.
RUN cp "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"
COPY php.ini "$PHP_INI_DIR/conf.d/zz-maleglitch.ini"

# Serve www/ as the document root; share/ sits next to it, as on the live host.
ENV APACHE_DOCUMENT_ROOT=/var/www/maleglitch/www
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
		/etc/apache2/sites-available/*.conf \
		/etc/apache2/apache2.conf \
		/etc/apache2/conf-available/*.conf

WORKDIR /var/www/maleglitch
