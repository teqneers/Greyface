# Development image for Greyface.
#
# FrankenPHP bundles the web server (Caddy) and PHP in one process, so this
# single image replaces the nginx + php-fpm pair, and Caddy's internal CA issues
# a locally-trusted certificate without any manual openssl work.
#
# This image is for development and testing only. Production deployment still
# uses docker/production/Dockerfile, which is self-contained.
#
# The source is bind-mounted rather than copied in — see compose.yaml.

FROM dunglas/frankenphp:1-php8.3

# ctype, iconv and mbstring are already compiled into the base image; composer.json
# `replace`s their polyfills, so they have to be present.
#   pdo_mysql  the application's only database driver
#   intl       used by symfony/validator's stricter formats
#   opcache    keeps the dev server usable
#   zip        composer needs it to install from dist archives
#   pcov       code coverage (much faster than xdebug for the same job)
#   xdebug     step debugging; inert unless XDEBUG_MODE says otherwise
RUN install-php-extensions \
        pdo_mysql \
        intl \
        opcache \
        zip \
        pcov \
        xdebug

COPY --from=composer/composer:2-bin /composer /usr/bin/composer

COPY docker/frankenphp/conf.d/app.ini /usr/local/etc/php/conf.d/zz-greyface.ini
COPY docker/frankenphp/Caddyfile /etc/frankenphp/Caddyfile

# The repository root is the Symfony project dir: public/index.php derives
# APP_PATH as dirname($script, 3), so the application has to sit at
# <root>/app/public for caches and logs to land in <root>/var.
WORKDIR /srv/greyface/app

# yarn/node are deliberately absent. The frontend build still runs on the host
# for now; app/public/build is bind-mounted in and served as static files.
