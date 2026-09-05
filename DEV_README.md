# Developing Greyface

Setting up a working copy. For what Greyface is, see the [README](README.md); for running it in
production, see [Operating Greyface](docs/operating.md).

**You do not need SQLGrey installed to work on Greyface.** The fixtures create SQLGrey's tables and
some sample data for you, using the same schema SQLGrey 1.8.0 creates, so development and test
behave the way production does.

## What you need

1. Docker and the Compose plugin
2. Node 22.19 or newer, and Yarn — the frontend still builds on the host
3. Git

PHP, Composer and MariaDB all come from the container.

## Getting started

### 1. Clone and start the stack

```bash
git clone https://github.com/teqneers/Greyface.git
cd Greyface
docker compose -f docker/compose.yaml up -d
```

This runs FrankenPHP, which is Caddy and PHP in one container, plus MariaDB 11.2 as a service named
`database`. That hostname is what the committed `.env` and `.env.test` expect. See
[docker/README.md](docker/README.md) for the details, including the traps.

### 2. Configure

Create `.env.local` next to `.env` in the repository root:

```dotenv
APP_ENV=dev
APP_SECRET=<generate one: openssl rand -hex 32>
DATABASE_URL=mysql://greyface:greyface@database:3306/greyface
```

Use `database` as the host when PHP runs inside Docker, or `127.0.0.1:13306` from the host.

### 3. Install dependencies and build the frontend

```bash
docker compose -f docker/compose.yaml exec php composer install
cd app && yarn install && yarn build && cd ..
```

### 4. Build the database

The order matters, because it mirrors a real installation: SQLGrey's tables exist first, then
Greyface adds its own and matches their collation.

```bash
docker compose -f docker/compose.yaml exec php bin/console greyface:fixtures:load --schema-only
docker compose -f docker/compose.yaml exec php bin/console doctrine:migrations:migrate
docker compose -f docker/compose.yaml exec php bin/console greyface:fixtures:load
```

That leaves you with SQLGrey's schema, five sample greylist entries, and an administrator
**admin** / **admin**. The fixtures refuse to run outside `dev` and `test`.

Greyface is now at <http://localhost:18080>, and on HTTPS at <https://localhost:18443>. HTTPS warns
about the certificate the first time; `docker/README.md` explains how to trust Caddy's local CA once.
The remember-me cookie is marked `secure`, so it is only issued over HTTPS.

### 5. Frontend development

```bash
cd app && yarn dev
```

Vite serves the modules with hot reload on port 15173 while you keep using
<http://localhost:18080>. Stop it with Ctrl+C and run `yarn build` to go back to a static build.

## Running the tests

Inside the container the committed `.env.test` already works:

```bash
docker compose -f docker/compose.yaml exec php bin/phpunit                    # the whole suite
docker compose -f docker/compose.yaml exec php bin/phpunit tests/Domain/User/Security/UserVoterTest.php
docker compose -f docker/compose.yaml exec php bin/phpunit --filter testDeniesDeletingTheLastAdministrator
docker compose -f docker/compose.yaml exec php bin/phpunit --coverage-text
docker compose -f docker/compose.yaml exec -e DISABLE_DB_SETUP=1 php bin/phpunit   # skip the rebuild
```

To run them from the host, pass the database URL as a real environment variable, which beats every
`.env` file:

```bash
cd app
DATABASE_URL='mysql://root@127.0.0.1:13306/greyface_test' bin/phpunit
```

Do **not** put that in `.env.test.local`: the repository root is bind-mounted into the container, so
a host-shaped override would be read there too and send the container to a host it cannot reach.

The suite **drops and recreates** the database named in `DATABASE_URL`, so that value must always
point at a throw-away database.

CI enforces a minimum line coverage; the threshold is passed to `php bin/check-coverage.php` in
`.github/workflows/ci.yml`. It is a ratchet: raise it as coverage improves, never lower it to make a
build pass.

Frontend tests and checks:

```bash
cd app
yarn test    # vitest
yarn lint    # eslint, then tsc --noEmit against both tsconfigs
yarn build
```

## Building the production image

```bash
docker build -f docker/production/Dockerfile -t greyface:local .
```

Unlike the development stack, that image is self-contained: it compiles the frontend, installs
production-only PHP dependencies and warms the cache. Releases build the same file; see
`.github/workflows/release.yml`.

## Releasing

Push a tag like `v3.0.0`. The release workflow runs the suite against that commit, builds the
archives and a multi-architecture image, boots the image against a real SQLGrey schema, and only
then publishes anything. Running the workflow by hand from the Actions tab publishes a `nightly`
image and a rolling pre-release instead.

Add your change to the [changelog](CHANGELOG.md) under `Unreleased` as you go.

## Where things live

`CLAUDE.md` documents the architecture in depth: the per-aggregate backend layout, the frontend's
shared primitives, and the traps that are easy to reintroduce. Worth reading before a first change.
