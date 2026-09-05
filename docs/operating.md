# Operating Greyface

For whoever installs and runs Greyface. If you just want to use it, read
[Using Greyface](using.md) instead.

1. [Before you start](#before-you-start)
2. [Installing with the container](#installing-with-the-container) — the quickest route
3. [Installing from the archive](#installing-from-the-archive) — no container needed
4. [Tagged recipients](#tagged-recipients) — `anna+newsletter@` and who it belongs to
5. [First steps after installing](#first-steps-after-installing)
6. [Releasing mail from the greylist](#releasing-mail-from-the-greylist) — whitelist, blacklist, sender or domain
7. [Assigning addresses in bulk](#assigning-addresses-in-bulk) — paste, import, or sync from cron
8. [What Greyface changes in your database](#what-greyface-changes-in-your-database)
9. [Upgrading](#upgrading) and [backing up](#backing-up)
10. [When something is wrong](#when-something-is-wrong)

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
   to `index.php`. Greyface ships no `.htaccess`, so the rule goes in your server configuration.

   **nginx**, with PHP-FPM:

   ```nginx
   server {
       listen 80;
       server_name greyface.example.com;
       root /var/www/greyface-<version>/app/public;

       location / {
           try_files $uri /index.php$is_args$args;
       }

       location ~ ^/index\.php(/|$) {
           fastcgi_pass unix:/run/php/php8.3-fpm.sock;
           fastcgi_split_path_info ^(.+\.php)(/.*)$;
           include fastcgi_params;
           fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
           fastcgi_param DOCUMENT_ROOT $realpath_root;
           internal;
       }

       # Nothing else in the tree is a PHP entry point.
       location ~ \.php$ {
           return 404;
       }
   }
   ```

   **Apache**:

   ```apache
   <VirtualHost *:80>
       ServerName greyface.example.com
       DocumentRoot /var/www/greyface-<version>/app/public

       <Directory /var/www/greyface-<version>/app/public>
           AllowOverride None
           Require all granted
           FallbackResource /index.php
       </Directory>

       # The compiled frontend is static; do not route it through PHP.
       <Directory /var/www/greyface-<version>/app/public/build>
           FallbackResource disabled
       </Directory>
   </VirtualHost>
   ```

   Serve it over HTTPS. The remember-me cookie is marked `secure`, so on plain HTTP people are
   signed out again whenever their session ends.

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
   belongs to them, for example `anna@example.com`. One row per address. For more than a handful,
   see [Assigning addresses in bulk](#assigning-addresses-in-bulk) below.

A user with no aliases sees an empty list, because Greyface matches on the recipient address and
has not been told which addresses are theirs. That is the single most common reason for "it shows
me nothing".

Point them at [Using Greyface](using.md), which is written for people with no technical background.

## Releasing mail from the greylist

Every row on the greylist has an **Auto Whitelist** button. That is the one action ordinary users
can take on their own mail, and it does what a successful retry would have done anyway: it tells
SQLGrey to trust that sender from that sending address, and the mail comes through on the next
attempt.

Administrators get a caret beside that button with five more destinations, because copying an
address out of the greylist and into a list screen by hand is tedious:

| | This sender | The whole domain |
|---|---|---|
| **Trust from this source** | the button itself | *Trust from 198.51.100* |
| **Never greylist** | *Never greylist* | *Never greylist* |
| **Always greylist** | *Always greylist* | *Always greylist* |

The distinction the list names hide is worth knowing, and it is why the menu is labelled by effect:

- The **auto-whitelist** is scoped to the sending address. It is SQLGrey's own learned-trust table,
  and entries in it age out.
- **Never greylist** (the whitelist, SQLGrey's `optout` tables) and **always greylist** (the
  blacklist, its `optin` tables) are permanent policy on an address or a domain, whatever machine
  the mail arrives from.

Anything covering a whole domain, and anything blacklisting, asks for confirmation first.
Whitelisting a single sender does not, because a toast offers an undo. The same destinations appear
in the selection bar when rows are ticked, so they work on fifty rows as well as one.

None of this is available to ordinary users: the lists behind it are administrators-only, so they
see no caret at all.

## Assigning addresses in bulk

Typing addresses one at a time is the reason most installations never hand Greyface to their users
at all. Two ways not to.

### From the interface

**Administration → Alias → Import** takes either shape:

- **A plain list of addresses**, all belonging to one account you pick from the dropdown. This is
  the paste-a-list case.
- **A two-column list** naming an account per address, which is what an export from your mail
  system looks like.

Either can be pasted or loaded from a file. Separate the columns with a comma, a tab or spaces;
blank lines and anything after a `#` are ignored.

```
anna@example.com,anna
sales@example.com,anna      # shared, but anna handles it
bob@example.com,bob
```

**Nothing is written until you have seen a preview.** *Check* reports what would change — how many
addresses are new, which are changing hands, which would be removed, and which lines it could not
read and why, with line numbers. *Apply* then does exactly that.

### From the command line

The same import, so it can run from cron and stay in step with your mail system without anyone
opening a browser:

```bash
# archive
php app/bin/console greyface:alias:import aliases.csv --dry-run   # report only
php app/bin/console greyface:alias:import aliases.csv             # apply

# container: the file has to be reachable from inside it, so pipe it in
docker exec -i greyface php bin/console greyface:alias:import - --dry-run < aliases.csv
```

`--user anna` treats the file as a plain list of addresses for one account. `-` reads standard
input. Add `--prune` to make it a sync rather than a one-off: addresses the file no longer names
are removed.

Greyface deliberately does not read Postfix's own lookup tables. Those can be flat files, `hash:`
or `lmdb:` databases, MySQL or LDAP, and reading them would mean reimplementing `postmap` and still
failing on half of them. One line of shell turns any of them into what the import wants:

```bash
postmap -s hash:/etc/postfix/virtual | awk '{print $1 "," $2}' > aliases.csv
```

### Three things it will not surprise you with

- **It never creates accounts.** A username in the file that Greyface does not recognise is
  reported for that line and skipped. A typo in a mail map must not become a login.
- **The file decides who owns the addresses it names.** An address currently assigned to somebody
  else is moved, not rejected, and both the preview and the command list every such move by name.
- **`--prune` only touches the accounts the file mentions.** A list covering anna and bob syncs
  anna and bob and leaves everyone else alone, so a partial list is safe to use.

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
