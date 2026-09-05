# Operating Greyface

For whoever installs and runs Greyface. If you just want to use it, read
[Using Greyface](using.md) instead.

## Before you start

Greyface is a window onto SQLGrey's database. It needs SQLGrey to be installed and working first.

It adds its own tables to that database, for accounts and address ownership. It **never creates,
alters or deletes a SQLGrey table**, and it never writes a greylist entry that did not come from
SQLGrey. If you point it at a database with no SQLGrey tables, it refuses to start and tells you so,
rather than creating an empty schema that would look right and behave wrongly.

You will need:

- SQLGrey running, with its tables in MariaDB 10.11 or newer
- The database name, host, and a user that can read and write it
- Somewhere to run Greyface: a container host, or a machine with PHP 8.3 or newer

Greyface does not have to run on the mail server. It only needs to reach the database.

## Installing with the container

The image is `ghcr.io/teqneers/greyface`. Pick a tag: `3` follows every 3.x release, `3.1` follows
patches of that minor, `3.1.0` never moves, and `latest` follows everything.

```bash
docker run -d --name greyface --restart unless-stopped -p 8080:80 \
    -e DATABASE_URL='mysql://greyface:password@db.internal:3306/sqlgrey' \
    -e APP_SECRET='...' \
    -e GREYFACE_ADMIN_USER=admin \
    -e GREYFACE_ADMIN_PASSWORD='choose-something-long' \
    ghcr.io/teqneers/greyface:3
```

There is a fuller example, with comments, at
[`docker/production/compose.example.yaml`](../docker/production/compose.example.yaml).

### Settings

| Variable | Required | What it does |
|---|---|---|
| `DATABASE_URL` | yes | The database SQLGrey uses, as `mysql://user:password@host:3306/dbname` |
| `APP_SECRET` | yes | Signs session and remember-me cookies. Generate with `openssl rand -hex 32` |
| `GREYFACE_ADMIN_USER` | first run | Creates the first administrator. Ignored once any account exists |
| `GREYFACE_ADMIN_PASSWORD` | first run | Its password |
| `GREYFACE_ADMIN_EMAIL` | no | Defaults to `<username>@greyface.local`; change it later in the interface |
| `GREYFACE_AUTO_MIGRATE` | no | `true` by default. Set `false` to run Greyface's migrations yourself |
| `SERVER_NAME` | no | Leave unset for plain HTTP on port 80. Set a hostname for automatic HTTPS |
| `TRUSTED_PROXIES` | no | Set when behind a reverse proxy, or Greyface will not know the request was HTTPS |

Keep `APP_SECRET` somewhere safe. Changing it signs everyone out. Losing it is not a disaster, but
you will want the same value across restarts, and the same value on every replica if you run more
than one.

### Behind a reverse proxy

This is the common case, and the default. Greyface serves plain HTTP on port 80 inside the
container and expects your proxy to hold the certificate. Set `TRUSTED_PROXIES` to the proxy's
address, or Greyface will believe the connection is insecure and the session cookie will not stick.

### Letting Greyface handle HTTPS

Set `SERVER_NAME` to a public hostname and Greyface obtains a certificate itself. That needs ports
80 and 443 published, outbound network access, and a volume mounted on `/data` so the certificate
survives a restart.

## Installing from the archive

Use this when you would rather not run a container.

1. Download `greyface-<version>.tar.gz` from the
   [releases page](https://github.com/teqneers/Greyface/releases) and check it against the published
   `.sha256` file.
2. Unpack it somewhere the web server can read, for example `/var/www/greyface`.
3. Create `.env.local` next to the shipped `.env`, in the top directory:

   ```dotenv
   DATABASE_URL="mysql://greyface:password@localhost:3306/sqlgrey"
   APP_SECRET="paste the output of: openssl rand -hex 32"
   ```

4. Make `var/` writable by the web server user.
5. Check the configuration before going further:

   ```bash
   php app/bin/console greyface:check-config
   ```

   It names anything wrong. Fix it before continuing.

6. Create Greyface's own tables and the first account:

   ```bash
   php app/bin/console doctrine:migrations:migrate
   php app/bin/console greyface:user:create --username=admin --password='choose-something-long'
   ```

7. Point the web server's document root at `app/public` and send everything that is not a real file
   to `app/public/index.php`. Symfony documents the configuration for
   [Apache and nginx](https://symfony.com/doc/current/setup/web_server_configuration.html).

The archive already contains the compiled frontend and all PHP dependencies. You do not need
Composer, Node or Yarn on the server.

## What Greyface changes in your database

It creates and owns these, and nothing else:

| Table | Holds |
|---|---|
| `tq_users` | Accounts and password hashes |
| `tq_aliases` | Which mail addresses belong to which account |
| `rememberme_token` | Remember-me tokens |
| `messenger_messages` | Internal queue |
| `db_updates` | Which migrations have run |

It also runs one `ALTER TABLE` against its own `tq_aliases`, matching that table's collation to
SQLGrey's `connect` table. Greyface joins those two tables, and MariaDB refuses to compare columns
whose collations differ. SQLGrey's table is read to find out what to match; it is never altered.

## Upgrading

Take a database backup first. Then:

**Container.** Pull the new tag and recreate the container. It migrates on start unless you set
`GREYFACE_AUTO_MIGRATE=false`.

```bash
docker pull ghcr.io/teqneers/greyface:3
docker compose up -d
```

**Archive.** Unpack the new version alongside the old one, copy your `.env.local` across, run the
migrations, then switch the web server over.

```bash
php app/bin/console doctrine:migrations:migrate
```

Read the [changelog](../CHANGELOG.md) before a major version. Migrations only ever touch Greyface's
own tables, so an upgrade cannot damage your greylisting data.

## Backing up

Back up the database. That is everything: Greyface keeps no state on disk beyond a cache it can
rebuild.

```bash
mariadb-dump --single-transaction sqlgrey > sqlgrey-backup.sql
```

That dump contains SQLGrey's data as well as Greyface's, which is what you want, since they share
one database.

## When something is wrong

Run the configuration check. It is the same one the container runs at startup, and it explains
rather than just failing.

```bash
# container
docker exec greyface php bin/console greyface:check-config
# archive
php app/bin/console greyface:check-config
```

**"SQLGrey's tables are missing from this database."** You have pointed Greyface at the wrong
database, or SQLGrey has not run against this one yet. Greyface will not create those tables.

**"APP_SECRET is unset or still the placeholder from the repository."** Generate one with
`openssl rand -hex 32` and set it. The placeholder is published in the source, so anyone could forge
a login against an installation still using it.

**"Collation mismatch."** You will not normally see this, because the migrations fix it. If you have
disabled automatic migration, run `doctrine:migrations:migrate`.

**The greylist is empty but mail is being delayed.** Greyface shows what is in SQLGrey's `connect`
table. If that is empty, SQLGrey is either writing to a different database or not running.

**A user sees nothing.** Users only see mail addressed to an alias assigned to them. Add their
addresses under Administration, Aliases.

Container logs go to standard output as JSON:

```bash
docker logs greyface
```
