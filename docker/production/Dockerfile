# Production image for Greyface.
#
# Unlike docker/frankenphp/Dockerfile, which is a bare PHP runtime with the source
# bind-mounted over it, this image is self-contained: the frontend is compiled and
# the PHP dependencies installed during the build. It expects an external MariaDB
# holding SQLGrey's tables, because Greyface reads and writes SQLGrey's schema but
# does not own it.
#
# Build from the repository root:
#   docker build -f docker/production/Dockerfile -t greyface .

# --- 1. Compile the frontend -------------------------------------------------
# The build has to happen here rather than being copied from the host, because
# .dockerignore excludes app/public/build from the context on purpose.
FROM node:24-slim AS frontend

WORKDIR /build
COPY app/package.json app/yarn.lock ./
RUN yarn install --frozen-lockfile

COPY app/ ./
# Vite's `base`. Override when Greyface is served from a sub-path.
ARG PUBLIC_PATH=/build
RUN PUBLIC_PATH=${PUBLIC_PATH} yarn build

# --- 2. Install PHP dependencies ---------------------------------------------
FROM dunglas/frankenphp:1-php8.3 AS vendor

COPY --from=composer/composer:2-bin /composer /usr/bin/composer
RUN install-php-extensions pdo_mysql intl zip

WORKDIR /build
# Manifests first, so the dependency layer is reused whenever only source changed.
COPY app/composer.json app/composer.lock app/symfony.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction

COPY app/ ./
RUN composer dump-autoload --no-dev --classmap-authoritative \
 && rm -rf tests/ node_modules/

# --- 3. Runtime --------------------------------------------------------------
FROM dunglas/frankenphp:1-php8.3

# No xdebug and no pcov: both are development tools and both cost performance
# merely by being loaded.
RUN install-php-extensions pdo_mysql intl opcache zip \
 && apt-get update && apt-get install -y --no-install-recommends curl libcap2-bin \
 && rm -rf /var/lib/apt/lists/*

COPY docker/production/Caddyfile /etc/frankenphp/Caddyfile
COPY docker/production/conf.d/app.ini /usr/local/etc/php/conf.d/zz-greyface.ini
COPY docker/production/entrypoint.sh /usr/local/bin/greyface-entrypoint
RUN chmod +x /usr/local/bin/greyface-entrypoint

# public/index.php derives the project directory as dirname($script, 3), so the
# application must sit three levels below it and var/ must be its sibling. Get
# this wrong and caches and logs land somewhere unexpected.
COPY --from=vendor  /build              /srv/greyface/app
COPY --from=frontend /build/public/build /srv/greyface/app/public/build

# Symfony's Dotenv insists on a .env existing next to the project directory, so
# this ships one — containing nothing but the environment name. Every real
# setting comes from the environment, and real environment variables win over
# this file. Secrets are never baked into a published image.
COPY docker/production/env /srv/greyface/.env

WORKDIR /srv/greyface/app

# Warm the container and generate Doctrine proxies now, so the first request does
# not pay for it. The dummy values only satisfy compilation; every %env()% stays
# a runtime placeholder and is read from the real environment when serving.
RUN APP_ENV=prod \
    APP_SECRET=build-time-placeholder-not-used-at-runtime \
    DATABASE_URL=mysql://build@127.0.0.1:3306/build \
    php bin/console cache:warmup --env=prod --no-debug \
 && APP_ENV=prod php bin/console assets:install public --no-interaction --env=prod

# Run unencumbered by root. Binding port 80 as a normal user needs the capability
# granted explicitly.
RUN setcap CAP_NET_BIND_SERVICE=+eip /usr/local/bin/frankenphp \
 && groupadd -r greyface && useradd -r -g greyface -d /srv/greyface greyface \
 && mkdir -p /srv/greyface/var/log /data /config \
 && chown -R greyface:greyface /srv/greyface /data /config

USER greyface

EXPOSE 80
EXPOSE 443

# Proves PHP is serving, not merely that Caddy started. A redirect to HTTPS,
# which is what happens once SERVER_NAME names a real host, counts as healthy.
HEALTHCHECK --interval=30s --timeout=5s --start-period=20s --retries=3 \
    CMD curl -fsS -o /dev/null http://127.0.0.1:80/login || exit 1

ENTRYPOINT ["greyface-entrypoint"]
CMD ["--config", "/etc/frankenphp/Caddyfile", "--adapter", "caddyfile"]
