GREYFACE
========

Greyface is an open source React based web interface to SQLGrey, a greylisting policy daemon for the Postfix MTA.
View and manipulate live greylisting data through an easy-to-use interface or submit new greylisting data.

Greyface is an open source web application developed by TEQneers, which interacts with SQLGrey and Postfix and
out-of-box. It builds on the greylisting approach and helps users and system administrators in managing their e-mails.

The old Greyface version 1 can still be found here: http://sourceforge.net/projects/greyface/
The new version is a complete refactoring of the old version and uses Symfony and React.


THE APPROACH
============

Greylisting is a method to SPAM-protect e-mails. By using greylisting on a mail server,
currently around 95% of potential spam can be blocked. The mail server compares the incoming e -mail with a database.
If the combination of the sender's e- mail address, recipient's e -mail address and the client IP address is not
yet stored in the database, the e-mail is set in a wait state. If this 3-match-combination is detected by the mail
server again. in a period of time, the e -mail will be forwarded to the recipient. It is set to the auto-whitelist.

If this combination of 3 is not detected in a given period , it is removed from the database.


Sometimes, however, an e-mail in the queue remains there without the receivers will.
Especially after a customer discussion you do not want to wait for the customer e- mail for a long time.
The other way would be perhaps the incoming e -mails will be permanently placed on the whitelist.
But each time addressing the system administrator would be time and cost intensive.

Greyface takes this administration off their hands.



FULL CONTROL FOR USERS AND SYSTEM ADMINISTRATORS
================================================

The user management of Greyface provides two user roles: system administrators and users.
This does not only guarantee highest privacy but also easy editing their emails.
System administrators have full access to the system. These include the following points:

    -WHITELIST: The whitelist determines which emails shall be forwarded without
                permission of the recipient.

    -BLACKLIST: The blacklist defines which e-mail addresses will be permanently
                blocked from the system.

    -GREYLIST: The greylist includes all e-mails that are in the queue.

    -USER MANAGEMENT: New users can be added or edited by the user management functions.
                      E-mail addresses and aliases can be managed.

Created users in the system have access to their greylist and have the opportunity to put emails directly to the
whitelist.



TECHNICAL REALIZATION
=====================
Greyface is written in Symfony 7.1 and PHP 8.3, offering a connection to the supplied database of SQLGrey.
It uses React 18 for the user interface, which keeps the application responsive and quick to work in.



INTERESTED?
===========
The latest version of Greyface can be found on https://github.com/teqneers/Greyface

## Technical Requirements
1. PHP 8.3 or higher, with the `ctype`, `iconv` and `pdo_mysql` extensions
2. Composer 2
3. Yarn
4. Docker and the Compose plugin

> MariaDB is required rather than MySQL. The SQLGrey tables use
> `DEFAULT "0000-00-00 00:00:00"`, which MySQL rejects under its default
> `NO_ZERO_DATE` / `STRICT_TRANS_TABLES` sql_mode.

## Setup

### 0. Prerequisites
Greyface is a Symfony and mysql based admin tool on top of sqlgrey. Due to this fact it is required that you
download and install sqlgrey in a php/mysql environment!

0.1 Download and install sqlgrey from http://sqlgrey.sourceforge.net/ \
0.2 Provide a mySQL installation and combine it with sqlgrey.


### 1. Clone the project

```bash
git clone https://github.com/teqneers/Greyface.git
cd Greyface
```

The Symfony application lives in `app/`, but the *project root* is the repository
root: `.env` is read from there, and caches and logs are written to
`<root>/var/`.

### 2. Start the stack

```bash
docker compose -f docker/compose.yaml up -d
```

This brings up FrankenPHP (Caddy + PHP in one container) and MariaDB 11.2 as a
service named `database`, matching the hostnames already committed in `.env` and
`.env.test`. See [docker/README.md](docker/README.md) for the details.

### 3. dotenv configuration

Create a `.env.local` next to `.env` in the repository root:

```dotenv
APP_ENV=dev
APP_SECRET=<<the application secret>> # http://nux.net/secret
DATABASE_URL=mysql://greyface:greyface@127.0.0.1:3306/greyface
```

Use the hostname `database` instead of `127.0.0.1` when PHP itself runs inside
Docker.

Ensure that `var/cache` and `var/log` in the repository root are writable.

### 4. Install dependencies

```bash
docker compose -f docker/compose.yaml exec php composer install
cd app && yarn install && yarn build   # the frontend still builds on the host
```

### 5. Database migrations

```bash
docker compose -f docker/compose.yaml exec php bin/console doctrine:migrations:migrate
```

The application is now at <http://localhost:18080>, and on HTTPS at
<https://localhost:18443>. HTTPS will warn about the certificate the first time;
`docker/README.md` explains how to trust Caddy's local CA once and be done with
it. Note that the "remember me" cookie is marked `secure`, so it is only issued
over HTTPS.

This creates Greyface's own tables and, if they do not already exist, the
SQLGrey tables — so an empty database is enough for development, with no
SQLGrey installation required.

A first administrator is created with the username **admin** and the password
**admin**. Change it immediately — the password hash is public in this
repository.

## Running the tests

Inside the container the committed `.env.test` already works — nothing to
configure:

```bash
docker compose -f docker/compose.yaml exec php bin/phpunit                              # the whole suite
docker compose -f docker/compose.yaml exec php bin/phpunit tests/Domain/User/Security/UserVoterTest.php
docker compose -f docker/compose.yaml exec php bin/phpunit --filter testDeniesDeletingTheLastAdministrator
docker compose -f docker/compose.yaml exec php bin/phpunit --coverage-text
docker compose -f docker/compose.yaml exec -e DISABLE_DB_SETUP=1 php bin/phpunit         # skip the schema rebuild
```

To run them from the host instead, pass the database URL as a real environment
variable — it beats every `.env` file:

```bash
cd app
DATABASE_URL='mysql://root@127.0.0.1:13306/greyface_test' bin/phpunit
```

Do **not** put that in a `.env.test.local`: the repository root is bind-mounted
into the container, so a host-shaped override would be read there too and send
the container to a host it cannot reach.

The suite **drops and recreates** the database named in `DATABASE_URL` before it
runs, so that value must always point at a throw-away database.

CI enforces a minimum line coverage; see the threshold passed to
`php bin/check-coverage.php` in `.github/workflows/ci.yml`. It is a ratchet —
raise it as coverage improves, never lower it to make a build pass.

### 5. Start server

`php bin/console server:start`