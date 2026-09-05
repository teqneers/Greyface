# Operating Greyface

For whoever installs and runs Greyface. If you just want to use it, read
[Using Greyface](using.md) instead.

1. [Before you start](#before-you-start)
2. [Installing with the container](#installing-with-the-container) — the quickest route
3. [Installing from the archive](#installing-from-the-archive) — no container needed
4. [Tagged recipients](#tagged-recipients) — `anna+newsletter@` and who it belongs to
5. [First steps after installing](#first-steps-after-installing)
6. [What Greyface changes in your database](#what-greyface-changes-in-your-database)
7. [Upgrading](#upgrading) and [backing up](#backing-up)
8. [When something is wrong](#when-something-is-wrong)

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

The image is `ghcr.io/teqneers/greyface`, built for both amd64 and arm64. It listens on port 80,
so publish it wherever you like with `-p`.

Which tag to pick depends on how much you want an upgrade to happen on its own:

| Tag | Moves to | Use it when |
|---|---|---|
| `3.1.0` | never | you want to decide every upgrade yourself |
| `3.1` | patches of 3.1 | you want fixes but no new features |
| `3` | every 3.x release | you want features too, but no major version jump |
| `latest` | everything, including major versions | you are trying it out |

Pre-releases such as `3.0.0-rc1` are published under their exact version only. They never move
`3`, `3.0` or `latest`, so pinning loosely will not drag you onto a release candidate.

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
| `GREYFACE_RECIPIENT_DELIMITER` | no | Your MTA's `recipient_delimiter`, `+` by default. Set it empty to match recipients exactly |

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

1. Download `greyface-<version>.tar.gz` and `greyface-<version>.sha256` from the
   [releases page](https://github.com/teqneers/Greyface/releases), and check the one against the
   other:

   ```bash
   sha256sum -c --ignore-missing greyface-<version>.sha256
   # macOS: shasum -a 256 -c --ignore-missing greyface-<version>.sha256
   ```

   The checksum file covers both the `.tar.gz` and the `.zip`, so `--ignore-missing` keeps it from
   complaining about the one you did not download. You want to see `OK`.

2. Unpack it somewhere the web server can read. It expands into a single
   `greyface-<version>/` directory containing `app/` and `var/`:

   ```bash
   tar -xzf greyface-<version>.tar.gz -C /var/www/
   ```

3. Create `.env.local` inside that directory, next to the shipped `.env`. Do not edit `.env`
   itself; an upgrade replaces it.

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

## Tagged recipients

Postfix delivers `anna+newsletter@example.com` to `anna@example.com` when its
`recipient_delimiter` is set, and Greyface follows that: whoever owns `anna@example.com` also sees
and can release mail addressed to any tagged form of it. They do not need an alias per tag.

Greyface cannot read `main.cf`, so this is a setting of its own, `GREYFACE_RECIPIENT_DELIMITER`,
defaulting to `+`. **Set it to an empty string if your MTA has no `recipient_delimiter`**, because
on such a system `anna+newsletter@example.com` is a literal mailbox name that need not have
anything to do with `anna`, and treating it as hers would show her somebody else's mail.

The widening stops at the delivered address. Owning `info@example.com` does not confer
`infodesk@example.com`.

## First steps after installing

Sign in with the administrator account you created. You will see the greylist straight away, which
is everything SQLGrey is currently holding back across the whole server.

To let a colleague release their own mail without coming to you, two things are needed:

1. **An account.** Go to **Administration → Users**, create one, and give it the role **User**.
   Administrators see everything; users see only their own mail.
2. **Their addresses.** Go to **Administration → Alias** and add each full mail address that
   belongs to them, for example `anna@example.com`. One row per address.

A user with no aliases sees an empty list, because Greyface matches on the recipient address and
has not been told which addresses are theirs. That is the single most common reason for "it shows
me nothing".

Point them at [Using Greyface](using.md), which is written for people with no technical background.

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

**Archive.** Unpack the new version alongside the old one rather than over it, so you can switch
back by pointing the web server at the old directory again.

```bash
tar -xzf greyface-<new-version>.tar.gz -C /var/www/
cp /var/www/greyface-<old-version>/.env.local /var/www/greyface-<new-version>/
cd /var/www/greyface-<new-version>
chown -R www-data var/                       # whichever user your web server runs as
php app/bin/console doctrine:migrations:migrate
```

Then point the document root at the new `app/public` and reload the web server.

Read the [changelog](../CHANGELOG.md) before a major version. Migrations only ever touch Greyface's
own tables, so an upgrade cannot damage your greylisting data.

## Backing up

Back up the database. That is everything: Greyface keeps no state on disk beyond a cache it can
rebuild.

```bash
mariadb-dump -u root -p --single-transaction sqlgrey > sqlgrey-backup.sql
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

**The greylist page fails with "Illegal mix of collations".** Greyface's alias table and SQLGrey's
`connect` table are being compared with different collations. Running the migrations fixes it, so
you only see this if you set `GREYFACE_AUTO_MIGRATE=false` and have not run them yet:

```bash
php app/bin/console doctrine:migrations:migrate
```

**The greylist is empty but mail is being delayed.** Greyface shows what is in SQLGrey's `connect`
table. If that is empty, SQLGrey is either writing to a different database or not running.

**A user sees nothing.** Users only see mail addressed to an alias assigned to them. Add their
addresses under **Administration → Alias**.

Container logs go to standard output as JSON:

```bash
docker logs greyface
```
