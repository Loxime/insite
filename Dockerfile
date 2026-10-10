# ============================================================
# MinIO - build
# ============================================================

FROM golang:1.24-alpine AS minio-builder

ARG MINIO_VERSION=RELEASE.2025-10-15T17-29-55Z

RUN apk add --no-cache \
    ca-certificates \
    git

RUN GOBIN=/out go install \
    github.com/minio/minio@${MINIO_VERSION}


# ============================================================
# MinIO - runtime
# ============================================================

FROM alpine:3.22 AS minio

RUN apk add --no-cache \
    ca-certificates \
    tzdata

COPY --from=minio-builder /out/minio /usr/local/bin/minio

VOLUME ["/data"]

EXPOSE 9000 9001

ENTRYPOINT ["minio"]

CMD ["server", "/data", "--console-address", ":9001"]


# ============================================================
# InSite - PHP / Apache
# ============================================================


FROM node:24-alpine AS frontend

WORKDIR /app

COPY package.json package-lock.json ./

RUN npm ci

COPY assets ./assets
COPY vite.config.ts tsconfig.json ./

RUN npm run build



FROM php:8.4-apache AS web-base

RUN apt-get update && apt-get install -y \
    git \
    curl \
    zip \
    unzip \
    libpng-dev \
    libzip-dev \
    libpq-dev \
    postgresql-client \
    && rm -rf /var/lib/apt/lists/*


RUN docker-php-ext-install \
    pdo_pgsql \
    zip \
    gd

RUN a2enmod rewrite

WORKDIR /var/www/html

COPY . /var/www/html

COPY --from=frontend /app/public/build /var/www/html/public/build



COPY --from=composer:latest \
    /usr/bin/composer \
    /usr/bin/composer

RUN mkdir -p var/cache var/log \
    && chown -R www-data:www-data var

RUN sed -i \
    's!/var/www/html!/var/www/html/public!g' \
    /etc/apache2/sites-available/000-default.conf

EXPOSE 80

CMD ["apache2-foreground"]


# ============================================================
# Development
# ============================================================

FROM web-base AS web

ENV APP_ENV=dev
ENV APP_DEBUG=1

RUN COMPOSER_ALLOW_SUPERUSER=1 composer install \
    --no-scripts \
    --no-interaction \
    --prefer-dist


# ============================================================
# Production
# ============================================================

FROM web-base AS web-prod

ENV APP_ENV=prod
ENV APP_DEBUG=0

RUN COMPOSER_ALLOW_SUPERUSER=1 composer install \
    --no-dev \
    --no-scripts \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader \
    && rm -rf tests phpunit.xml.dist

# Build the official MinIO client from a pinned release
FROM golang:1.24-alpine AS minio-mc-builder

RUN apk add --no-cache ca-certificates git

ARG MC_VERSION=RELEASE.2025-08-13T08-35-41Z

RUN CGO_ENABLED=0 GOBIN=/out \
    go install github.com/minio/mc@${MC_VERSION}


# MinIO provisioning client with a POSIX shell
FROM alpine:3.22 AS minio-init

RUN apk add --no-cache ca-certificates

COPY --from=minio-mc-builder /out/mc /usr/local/bin/mc

RUN mc --version
