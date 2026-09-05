# Greyface

**Find the mail your server is holding back, and let it through.**

[![CI](https://github.com/teqneers/Greyface/actions/workflows/ci.yml/badge.svg)](https://github.com/teqneers/Greyface/actions/workflows/ci.yml)
[![Latest release](https://img.shields.io/github/v/release/teqneers/Greyface?include_prereleases&sort=semver)](https://github.com/teqneers/Greyface/releases)
[![Licence: MIT](https://img.shields.io/badge/licence-MIT-blue)](LICENSE)

Mail servers running [SQLGrey](http://sqlgrey.sourceforge.net/) turn away the first message from
any sender they have never seen before. That is greylisting, and it stops a great deal of spam.
Now and then it also delays a mail somebody is actually waiting for, and then somebody has to go
and release it.

Greyface is the web interface for exactly that. Administrators see everything being held.
Everyone else signs in, sees only the mail addressed to *their own* addresses, and releases it
with one click, without raising a ticket and without waiting for you.

```bash
docker run -d --name greyface -p 8080:80 \
    -e DATABASE_URL='mysql://user:password@your-db-host:3306/sqlgrey' \
    -e APP_SECRET="$(openssl rand -hex 32)" \
    -e GREYFACE_ADMIN_USER=admin \
    -e GREYFACE_ADMIN_PASSWORD='choose-something-long' \
    ghcr.io/teqneers/greyface:3
```

That is the whole installation. Point it at the database SQLGrey already uses, open
<http://localhost:8080>, and sign in.

## Why mail gets delayed in the first place

Greylisting costs almost nothing to run and removes most spam.

When a message arrives from a combination of sender and server your system has never seen,
SQLGrey replies "try again later" instead of accepting it. Real mail servers treat that as
routine and retry a few minutes later, and the message goes through. Spam senders usually
move on and never come back.

The price is that first delay. Usually nobody notices. Sometimes it lands on the one message
someone is standing over their inbox waiting for, and that is the moment Greyface is for.

## What you get

**If you run the mail server**

- Everything SQLGrey is currently holding, in one searchable list
- The senders SQLGrey has learned to trust, and the whitelist and blacklist, all editable
- Accounts, and which mail addresses belong to whom
- A dashboard showing what is waiting and what has been happening

**If you just receive mail**

- Only what is being held for your own addresses, never anyone else's
- One click to let a sender through, with an undo if you misclick
- Plain-language help on the page itself, no manual required

All of it in English and German, on a phone or a desktop, in light or dark mode.

## What you need

Greyface reads and writes SQLGrey's database. It does not install, configure or replace SQLGrey,
and it never creates or alters SQLGrey's own tables.

- A working **SQLGrey** installation, with its tables in **MariaDB 10.11 or newer**
- Somewhere to run Greyface: a container host, or a machine with **PHP 8.3 or newer**
  (`ctype`, `iconv`, `intl`, `pdo_mysql`). The container brings its own.

It does not have to run on the mail server itself. It only needs to reach the database.

MariaDB rather than MySQL: SQLGrey's schema uses `timestamp` columns that MySQL rejects under its
default `NO_ZERO_DATE` mode.

## Installing without Docker

Download `greyface-<version>.tar.gz` from the
[releases page](https://github.com/teqneers/Greyface/releases) and unpack it into a web root. It
already contains everything compiled, so the server needs no Composer, Node or Yarn. The
[operating guide](docs/operating.md) walks through it.

If anything is misconfigured, Greyface refuses to start and says precisely what is wrong, rather
than coming up broken.

## Documentation

- **[Operating Greyface](docs/operating.md)** — installing, configuring, upgrading, backing up,
  and what to check when something looks wrong
- **[Using Greyface](docs/using.md)** — for the people who log in daily, in plain language
- **[DEV_README.md](DEV_README.md)** — working on Greyface itself
- **[Changelog](CHANGELOG.md)** — what changed, written for whoever does the upgrading

## Where this comes from

Greyface is built by [TEQneers](https://www.teqneers.de/). Version 1 lived on
[SourceForge](http://sourceforge.net/projects/greyface/) and is no longer maintained; version 2 was
a complete rewrite on Symfony and React, and version 3 is that rewrite with a new interface and a
real release process.

## Licence

MIT. See [LICENSE](LICENSE).
