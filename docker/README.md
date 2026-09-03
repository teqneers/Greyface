# Docker setup

Development and test environment for Greyface. **There is deliberately no
production stack here** — releases still go through `app/files/build_tag.sh`.

```
docker/
  frankenphp/
    Dockerfile      the PHP + web server image
    Caddyfile       server config; document root is <root>/app/public
    conf.d/app.ini  PHP settings (development values)
  build/            generated, gitignored — see "Frontend" below
```

## Getting started

```bash
docker compose up -d
docker compose exec php composer install
docker compose exec php bin/console doctrine:migrations:migrate
```

Then open <http://localhost:18080> and log in with `admin` / `admin`.

Both schemes are served, on the ports below. Plain HTTP is the path of least
resistance; <https://localhost:18443> is there when you need to exercise
anything TLS-dependent — note that the "remember me" cookie is marked `secure`
in `security.yaml`, so it is only issued over HTTPS.

HTTPS will warn about the certificate the first time. Caddy issues it from its
own local CA, which you can trust once to make the warning go away:

```bash
docker compose cp php:/data/caddy/pki/authorities/local/root.crt /tmp/greyface-ca.crt
# macOS
sudo security add-trusted-cert -d -r trustRoot -k /Library/Keychains/System.keychain /tmp/greyface-ca.crt
```

The CA lives in a named volume, so it survives rebuilds and only has to be
trusted once.

## Why one container

FrankenPHP bundles Caddy and PHP in a single process, which replaces the usual
nginx + php-fpm pair and — more usefully here — issues a locally-trusted TLS
certificate with no manual `openssl` work.

Worker mode is *not* enabled. It would need `runtime/frankenphp-symfony` and a
change to `public/index.php`; that is worth doing after the Symfony 7.4 upgrade,
not during it.

## Ports

Published on a `1xxxx` prefix so this stack coexists with the other project
stacks on the same machine:

| Service  | Port    | Override        |
|----------|---------|-----------------|
| php      | `18443` | `HTTPS_PORT`    |
| php      | `18080` | `HTTP_PORT`     |
| database | `13306` | `DATABASE_PORT` |

Put overrides in a `.env` file next to `compose.yaml`.

Caddy binds these ports **directly**, and compose publishes them 1:1 rather than
mapping 80/443 through. That is deliberate: Caddy builds redirects from the site
address, so listening on 80 behind a `18080:80` mapping made every redirect drop
the port and send browsers to `https://localhost/` — a page that does not
exist. The site addresses in the Caddyfile carry their port for the same reason.

## Running the tests

**Inside the container — the simple path.** The committed `.env.test` already
points at `database:3306`, which resolves here, so nothing else is needed:

```bash
docker compose exec php bin/phpunit
docker compose exec php bin/phpunit --filter testDeniesDeletingTheLastAdministrator
docker compose exec php bin/phpunit --coverage-text
```

**From the host.** Pass the URL as a real environment variable — it beats every
`.env` file:

```bash
cd app
DATABASE_URL='mysql://root@127.0.0.1:13306/greyface_test' bin/phpunit
```

> Do **not** put that in a `.env.test.local`. The repository root is bind-mounted
> into the container, so a host-shaped override would be read there too and point
> the container at a host it cannot reach.

The suite **drops and recreates** the database named in `DATABASE_URL`, so it
must always be a throw-away one.

Editing `Caddyfile` or `conf.d/app.ini` only needs `docker compose restart php`
— both are bind-mounted over the copies baked into the image.

## Three things that will bite you

**`APP_ENV` must not be set as a container environment variable.** PHP runs with
`variables_order=EGPCS`, so a compose `environment:` entry lands in `$_ENV` —
and Symfony's `KernelTestCase` reads `$_ENV` *before* the `$_SERVER` value
`phpunit.xml.dist` forces. Setting it silently runs the entire test suite in the
`dev` environment, where `framework.test` is off and every functional test dies.
The committed `.env` selects `dev` for normal requests; leave it to.

**`var/` is a named volume, not a bind mount.** Symfony bakes absolute paths into
the compiled container, so a cache built by the host at `/Users/...` is invalid
at `/srv/greyface` and vice versa. Sharing the directory makes the two rebuild
over each other's work. Read the container's logs with:

```bash
docker compose exec php cat ../var/log/dev.log
```

## Path layout

The repository root is mounted at `/srv/greyface`, not the `app/` directory.
`app/public/index.php` derives `APP_PATH` as `dirname($script, 3)`, so the
application has to sit at `<root>/app/public` for caches and logs to land in
`<root>/var`.

## Frontend

Not containerised yet — keep running `yarn` on the host. Encore writes to
`app/public/build`, which is bind-mounted in and served as static files, so a
host-side `yarn build` or `yarn watch` shows up immediately.

`yarn start` (the Encore dev-server with hot reload) still does **not** work from
a clean checkout: `app/webpack.config.js` reads a TLS key pair from
`docker/build/webserver/`, which nothing generates. That is why `docker/build/`
is gitignored while the rest of this directory is committed.

## Not covered here

`app/files/build.sh` and `app/files/build_tag.sh` are the release scripts. They
reference a `github.com/teqneers/greyface/php:dev` image and a
`docker/common/_env_loader.sh` that were never committed, so both are still
broken from a clean checkout. Left untouched on purpose — they may still be
wired into a build job.
