# Changelog

Notable changes, written for the person doing the upgrading. The format follows
[Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and Greyface uses
[semantic versioning](https://semver.org/).

Add your change under `Unreleased` as you make it. Cutting a release turns that heading into a
version.

## [Unreleased]

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
- The running version is shown in the account menu.
- Operator and user guides under `docs/`.

### Changed

- **Greyface no longer creates or seeds SQLGrey's tables.** They belong to SQLGrey, and creating
  them meant a fresh install could add fake greylist entries to a live mail filter. Greyface now
  refuses to start against a database with no SQLGrey tables rather than inventing them.
- **No default account ships any more.** Previously a migration created `admin` / `admin` from a
  password hash published in this repository. A fresh installation creates its first administrator
  from the environment instead.
- The frontend was rebuilt on Tailwind and shadcn/ui, with a dashboard, dark mode, bulk actions and
  a rewritten navigation. The whitelist, blacklist and auto-whitelist screens each merged from two
  screens into one with tabs; the old `/opt-out`, `/opt-in` and `/awl` URLs redirect.
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

[Unreleased]: https://github.com/teqneers/Greyface/compare/2.0.1...HEAD
[2.0.1]: https://github.com/teqneers/Greyface/compare/releases/2.0.0-1...2.0.1
[2.0.0]: https://github.com/teqneers/Greyface/releases/tag/releases/2.0.0-0
