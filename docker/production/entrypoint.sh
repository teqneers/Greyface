#!/bin/sh
#
# Runs before Greyface serves anything.
#
# The order matters: refuse an unsafe configuration first, so a misconfigured
# container never touches the database at all.
set -e

cd /srv/greyface/app

# Fails, loudly and specifically, on a missing DATABASE_URL, an unreachable
# database, or the placeholder APP_SECRET from the repository.
php bin/console greyface:check-config

# Greyface's own tables only. Its migrations never create, alter or seed
# SQLGrey's tables — those belong to SQLGrey and are left exactly as they are.
# Set GREYFACE_AUTO_MIGRATE=false to take that step into your own hands.
if [ "${GREYFACE_AUTO_MIGRATE:-true}" != "false" ]; then
    php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration
fi

# Creates the first administrator on a fresh database and does nothing
# afterwards. Without GREYFACE_ADMIN_USER and GREYFACE_ADMIN_PASSWORD no account
# is created: Greyface ships no default credentials, because this image is
# public and anything baked into it would be public too.
if [ -n "${GREYFACE_ADMIN_USER:-}" ]; then
    php bin/console greyface:user:create --if-none
fi

exec docker-php-entrypoint "$@"
