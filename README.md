# Greyface

A web interface to [SQLGrey](http://sqlgrey.sourceforge.net/), the greylisting policy daemon for
the Postfix mail server. Greyface lets you see which mail is being held back and release it, and it
lets your users do that for their own addresses without asking you.

Greyface is developed by [TEQneers](https://www.teqneers.de/) and is open source under the MIT
licence. Version 1 lived on [SourceForge](http://sourceforge.net/projects/greyface/); this is a
complete rewrite on Symfony and React.

## What greylisting does, briefly

When mail arrives from a sender your server has never seen, SQLGrey turns the first delivery
attempt away. A real mail server waits a few minutes and tries again, and the mail is accepted.
Most spam senders never come back, which is why greylisting removes a great deal of it for very
little effort.

The cost is delay, and occasionally the delay lands on a mail somebody is waiting for. Releasing
that mail is normally an administrator's job. Greyface hands it to whoever is actually waiting.

## What Greyface gives you

Administrators get the full picture: everything currently held, the senders SQLGrey has learned to
trust, and the whitelist and blacklist. They manage users and decide which mail addresses belong to
whom.

Everyone else sees only the mail held for their own addresses, and can release any of it with one
click.

## Requirements

Greyface reads and writes SQLGrey's database. It does not install, configure or replace SQLGrey,
and it never creates or alters SQLGrey's own tables.

- A working **SQLGrey** installation with its tables in **MariaDB 10.11 or newer**
- **PHP 8.3 or newer** with `ctype`, `iconv`, `intl` and `pdo_mysql` — unless you use the container,
  which brings its own

MariaDB rather than MySQL: SQLGrey's schema uses `timestamp` columns that MySQL rejects under its
default `NO_ZERO_DATE` mode.

## Getting it running

The fastest route, pointing at the database SQLGrey already uses:

```bash
docker run -d --name greyface -p 8080:80 \
    -e DATABASE_URL='mysql://user:password@your-db-host:3306/sqlgrey' \
    -e APP_SECRET="$(openssl rand -hex 32)" \
    -e GREYFACE_ADMIN_USER=admin \
    -e GREYFACE_ADMIN_PASSWORD='choose-something-long' \
    ghcr.io/teqneers/greyface:3
```

Then open <http://localhost:8080> and sign in. If anything is misconfigured the container refuses
to start and says exactly what is wrong, rather than starting up broken.

Prefer to install it on the mail server itself? Download the archive from the
[releases page](https://github.com/teqneers/Greyface/releases), unpack it, and follow the operating
guide.

## Documentation

- **[Operating Greyface](docs/operating.md)** — installing, configuring, upgrading and backing up,
  by container or from the archive
- **[Using Greyface](docs/using.md)** — for the people who log in daily, in plain language
- **[DEV_README.md](DEV_README.md)** — working on Greyface itself

## Licence

MIT. See [LICENSE](LICENSE).
