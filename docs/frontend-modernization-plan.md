# Frontend modernization plan

Agreed 2026-09-04. This document is the design record for replacing the Greyface v2
frontend; it says what was decided and why, so later work does not re-open settled
questions. Progress is tracked in the checklist at the end.

## Goal

Turn the Greyface frontend into a contemporary, accessible admin UI with its own
identity, without changing what the application does for SQLGrey.

## Decisions

### Scope and depth

- The React SPA is rebuilt on a new component layer. Login and password change stay
  server-rendered Twig pages, restyled with the same design tokens. Moving auth into the
  SPA was rejected: it is a backend change with real risk and no visible UX gain.
- The data layer is kept as is: React 19, `@tanstack/react-query` 5, `react-router` 7.
- Backend changes are additive only: new endpoints and new optional query parameters.
  No existing endpoint changes shape.
- One feature branch (`frontend-modernization`), incremental commits, merged once complete.
  Two component libraries never coexist in a release.

### Stack

| Concern | Choice | Why |
|---|---|---|
| Bundler | Vite via `pentatrion/vite-bundle`, output in `app/public/build` | vitest already runs on Vite; Encore's Babel setup bit us once (see CLAUDE.md); output path unchanged so `files/build_tag.sh` keeps working |
| Styling | Tailwind 4 | required by shadcn; tokens as CSS variables give dark mode for free |
| Components | shadcn/ui on Radix primitives, copied into `assets/js/components/ui` | components are owned code, so there is no future library migration |
| Tables | TanStack Table 8, server-side sort and pagination | successor of react-table 7, same manual mode |
| Forms | react-hook-form + zod | shadcn's documented pairing; replaces formik + yup |
| Toasts | sonner | |
| Icons | lucide-react | the app has no icon set today |
| Charts | Recharts through shadcn chart components | one dashboard chart, theming solved |
| Fonts | Inter, self-hosted via `@fontsource-variable/inter` | no runtime CDN; the app runs inside mail infrastructure |
| Types | strict TypeScript, `tsc --noEmit` in `yarn lint` and CI | types were stripped and never checked |
| Browsers | evergreen only (`last 2 versions`, iOS >= 15) | Tailwind 4 and Radix assume modern CSS |

All dependencies are self-hosted and MIT/Apache licensed. Unused packages are removed
(styled-components, @emotion/css, react-modal, react-table-sticky, urlcat,
content-disposition, prop-types, react-helmet-async, react-datepicker, bootstrap,
react-bootstrap, formik, yup, react-table, the Babel and webpack toolchain).

### Design

- Neutral palette with a single muted blue accent. The terracotta `#ab7967` is retired:
  it fights dark mode and reads dated.
- Greyface gets its own wordmark; the TEQneers logo leaves the header and a TEQneers
  credit stays in the footer.
- Dark mode follows the OS by default, with a light/dark/system toggle in the user menu,
  persisted in localStorage with the other settings.
- Accessibility baseline: focus management in dialogs, `aria-sort` on sortable headers,
  labelled row actions, `aria-current` on pagination, keyboard-reachable everything.
- Admins get a left sidebar with three groups: Greylist / Lists (whitelist, blacklist,
  auto-whitelist) / Administration (users, aliases). It collapses on narrow screens.
  Non-admin users see the greylist only and get a top bar, no sidebar.
- Admin console is designed for a desk; end-user views are responsive, not mobile-first.

### Screens

- **Dashboard** (admin landing): stat tiles (pending greylist entries, auto-whitelisted
  senders and domains, whitelist, blacklist, users) plus one 14-day activity chart of new
  greylist entries and auto-whitelist activity per day. Needs two additive endpoints:
  counts and daily buckets. Recent-item lists were rejected as duplicates of the greylist.
- **Greylist** (end-user landing): stats strip for the user's own pending and whitelisted
  counts; dismissible explanation callout (remembered per browser) plus a help icon opening
  the same text, which also explains the SQLGrey column names; "move to whitelist" acts
  immediately with an undo toast; delete asks for confirmation; delete-by-date becomes a
  dialog with presets (7/30/90 days) and a custom date, showing the affected count before
  confirming (list endpoint gains a `before` filter).
- **Whitelist, blacklist, auto-whitelist**: one screen each with an email/domain tab that is
  a route (`/whitelist/emails`, `/whitelist/domains/create`). Old paths (`/opt-out/*`,
  `/opt-in/*`, `/awl/*`) redirect. The URLs stop exposing SQLGrey's opt-in/opt-out jargon.
- **Users, aliases**: rebuilt, single-row actions only. Bulk delete of users is a foot-gun.
- **Bulk selection** on the greylist (whitelist selected, delete selected) and the three
  lists (delete selected). Select-all covers the current page only; page size goes up to
  100. Every bulk delete confirms with the count. Cross-page select was rejected: it needs
  filter-based bulk endpoints and a scarier confirmation.
- **Route guard**: non-admins hitting an admin route are sent to the greylist with a toast.
  Today admin routes are only hidden in the menu.

### State and i18n

- Filter, sort and page live in the URL, so a view can be shared and the back button works.
- Sort, page size and search are also remembered across logins in localStorage; the page
  number is remembered per browser session only (sessionStorage). When a module is opened
  without query parameters, the remembered state is applied to the URL. URL parameters
  always win.
- German and English stay. Dates and numbers follow the active locale; today they are
  always formatted German-style.

### Tests

- The existing five vitest files stay green through the change to `assets/js/test/render.tsx`.
- One new test each for the shared data table, the form dialog and the route guard.
- No per-screen tests; the screens are thin copies of each other.
- Backend: controller tests for every new endpoint, in the existing `WebTestCase` style.

## Order of work

1. **Tooling**: Vite bundle, Tailwind, shadcn init, strict tsconfig, tsc in CI, dependency
   cleanup, tests green on the new render helper.
2. **Shell**: tokens, dark mode, sidebar and top bar, route guard, toasts, Twig pages restyled.
3. **Shared primitives**: data table with bulk selection, form dialog, confirm dialog,
   empty and skeleton states.
4. **Screens**: greylist with user help and undo, then the three merged list screens, then
   users and aliases.
5. **Dashboard**: endpoints, tiles, chart.
6. **Polish**: accessibility pass, responsive pass on user views, translations review.

The greylist goes first among screens because every role uses it and it exercises every
primitive. The dashboard goes last because it is the only piece with backend work of its own.

## Checklist

- [x] 1 Tooling
- [x] 2 Shell
- [x] 3 Shared primitives
- [x] 4a Greylist
- [ ] 4b Whitelist, blacklist, auto-whitelist
- [ ] 4c Users, aliases
- [ ] 5 Dashboard
- [ ] 6 Polish
