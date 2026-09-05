# Changelog

Notable changes, written for the person doing the upgrading. The format follows
[Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and Greyface uses
[semantic versioning](https://semver.org/).

Add your change under `Unreleased` as you make it. Cutting a release turns that heading into a
version.

## [Unreleased]

### Added

- **A greylisted entry can now go to the whitelist or blacklist, not only the auto-whitelist**, for
  the sender or for its whole domain. A caret beside the existing button carries the five new
  destinations; the everyday action is unchanged and still one click. Administrators only, because
  those lists always were. Anything domain-wide, and anything blacklisting, asks first.
  ([#95](https://github.com/teqneers/Greyface/issues/95))
- **Bulk import of user aliases**, so addresses no longer have to be assigned one at a time. Paste
  a list for one account, or a two-column list naming an account per address, from the interface or
  from a file. Nothing is written until a preview has been shown.
  ([#76](https://github.com/teqneers/Greyface/issues/76))
- `greyface:alias:import`, the same import from the command line, with `--dry-run` and a `--prune`
  that makes it a sync rather than a one-off. Suitable for cron. It never creates accounts, and
  `--prune` only touches the accounts the file mentions, so a partial list is safe.
  See [Operating Greyface](docs/operating.md#assigning-addresses-in-bulk).

### Fixed

- Destructive confirmations rendered as ordinary primary buttons. Every "are you sure" in the
  application — deleting a greylist entry, a bulk delete, deleting a user or an alias — looked the
  same as a routine one.

## [3.1.0] - 2026-09-05

Upgrade promptly if you have given anyone but administrators an account. Also the tagged-recipient
support first asked for in 2014.

### Security

- **Any signed-in account could act on mail that was not theirs.** Authorization on the greylist
  was applied only to the listing, by the query that fills it. Every write endpoint took its
  identifiers from the request body and never checked who was asking, so an ordinary user could
  delete an entry addressed to somebody else — one their own listing never showed them — or empty
  the greylist for the entire server with a single request to `/api/greylist/delete-to-date`. The
  interface hid that last one from non-administrators; only the API was missing the check.

  Ownership is now enforced per row, on every endpoint, and bulk calls check each entry rather
  than once per batch. Deleting by date is administrators only.

  Anyone who has only ever created administrator accounts was never exposed.

### Fixed

- **Ordinary users could not release their own mail**, which is the one thing the product exists
  for. "Auto Whitelist" required an administrator-only permission, so a user clicking it got a
  403. The button was never hidden from them, so this was visibly broken rather than merely
  absent, and both the README and the user guide promised it worked.

### Added

- Mail sent to a tagged address (`anna+newsletter@example.com`) now belongs to whoever owns the
  address it is delivered to (`anna@example.com`), so they can see and release it without an alias
  per tag. New setting `GREYFACE_RECIPIENT_DELIMITER`, defaulting to `+`; set it to an empty
  string if your MTA has no `recipient_delimiter`, because there such an address is a literal
  mailbox name and need not belong to `anna`.
  ([#80](https://github.com/teqneers/Greyface/issues/80))
- Working nginx and Apache configuration in the operating guide. Greyface ships no `.htaccess`, so
  the previous pointer to Symfony's generic documentation left the rewrite rule as an exercise.
  ([#83](https://github.com/teqneers/Greyface/issues/83))

## [3.0.0] - 2026-09-05

A rebuilt interface, a real release process, and a clean separation between Greyface's tables and
SQLGrey's. Greyface itself still runs on PHP 8.3, Symfony 7.4 and Doctrine ORM 3, so the server
requirements are unchanged from 2.0.1.

### If you are upgrading from 2.x, read this first

- **Set your own `APP_SECRET`.** Version 2 shipped `.env` with a fixed value,
  `ff7cb5c00e05226de5813f3fe4efc70a`, published in this repository. If your installation never
  overrode it, anyone could forge a login against it. Generate one with `openssl rand -hex 32` and
  set it. Everyone will be signed out once, which is the point. Greyface now refuses to start on
  that value.
- **Change the `admin` password if you never did.** Version 2 created `admin` / `admin` from a
  password hash that is also public in this repository.
- **Take a database backup before migrating**, as with any upgrade. The migrations only touch
  Greyface's own tables, so greylisting data is not at risk, but the backup costs nothing.
- **No new `deploy/*` git tags are produced.** Version 2 was shipped by committing a built tree,
  `vendor/` included, into a tag. The existing `deploy/2.0.0-*` tags stay where they are, but 3.0.0
  and everything after it ships as a container image or a release archive instead. See
  [Operating Greyface](docs/operating.md).
- Bookmarks to `/opt-out`, `/opt-in` and `/awl` still work; they redirect to `/whitelist`,
  `/blacklist` and `/auto-whitelist`.

### Added

- Container images on `ghcr.io/teqneers/greyface`, for amd64 and arm64. The image is
  self-contained and expects an external SQLGrey database. See
  [Operating Greyface](docs/operating.md).
- Downloadable archives attached to each GitHub release, replacing the old `deploy/*` tags that
  committed built output into the repository. Unpack one into a web root and it runs; no Composer,
  Node or Yarn needed on the server.
- `greyface:check-config`, which refuses an unsafe configuration and explains why. The container
  runs it before serving; run it yourself as the last step of a manual install.
- `greyface:user:create`, which creates the first administrator from `GREYFACE_ADMIN_USER` and
  `GREYFACE_ADMIN_PASSWORD`.
- `greyface:fixtures:load`, which creates SQLGrey's tables and sample data for development and
  test. It refuses to run anywhere else.
- A dashboard, dark mode, bulk actions, and in-app help on the greylist screen.
- The running version is shown in the account menu.
- Operator and user guides under `docs/`.

### Changed

- **Greyface no longer creates or seeds SQLGrey's tables.** They belong to SQLGrey, and creating
  them meant a fresh install could add fake greylist entries to a live mail filter. Greyface now
  refuses to start against a database with no SQLGrey tables rather than inventing them.
- **No default account ships any more.** Previously a migration created `admin` / `admin` from a
  password hash published in this repository. A fresh installation creates its first administrator
  from the environment instead. Existing accounts are untouched.
- The frontend was rebuilt on Tailwind and shadcn/ui, with a rewritten navigation. The whitelist,
  blacklist and auto-whitelist screens each merged from two screens into one with tabs.
- The build moved from Webpack Encore to Vite.
- `composer.json` declared `proprietary` while the LICENSE file said MIT. It now says MIT.

### Fixed

- The greylist failed with "Illegal mix of collations" against a real SQLGrey database. Greyface
  forced `utf8mb4_unicode_ci` on its alias table while SQLGrey takes the database default, and the
  greylist joins those two columns. A migration now matches Greyface's own column to SQLGrey's.
  SQLGrey's table is only read, never altered.
- Rolling back the migration that created SQLGrey's tables would have dropped `connect`,
  `domain_awl`, `from_awl` and `config`, destroying a live SQLGrey installation. That migration no
  longer does anything in either direction.

### Removed

- `app/files/build.sh` and `app/files/build_tag.sh`. Both had been broken from a clean checkout for
  years: one sourced a file that was never committed, the other referenced an image that never
  existed. The release workflow replaces them.

## [2.0.1] - 2026-09-04

Dependency maintenance, including React 19.

## [2.0.0] - 2024-08-22

First release of the Symfony and React rewrite.

[Unreleased]: https://github.com/teqneers/Greyface/compare/v3.1.0...HEAD
[3.1.0]: https://github.com/teqneers/Greyface/compare/v3.0.0...v3.1.0
[3.0.0]: https://github.com/teqneers/Greyface/compare/2.0.1...v3.0.0
[2.0.1]: https://github.com/teqneers/Greyface/compare/releases/2.0.0-1...2.0.1
[2.0.0]: https://github.com/teqneers/Greyface/releases/tag/releases/2.0.0-0
