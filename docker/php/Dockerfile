FROM php:8.2-fpm-alpine

RUN apk add --no-cache \
		freetype-dev \
		libjpeg-turbo-dev \
		libpng-dev \
		libwebp-dev \
		zlib-dev \
	&& docker-php-ext-configure gd \
		--with-freetype \
		--with-jpeg \
		--with-webp \
	&& docker-php-ext-install pdo pdo_mysql gd

WORKDIR /var/www
