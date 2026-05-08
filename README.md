# Marrified Studio

**Architecture spike PoC** for a wedding-invitation engine built around 4 pillars:

1. **Section as first-class entity** — each invitation is a list of section instances (cover, quotes, couple, event, gallery, gift, rsvp, guestbook), each pointing to a layout variant.
2. **Theme = Asset Pack + Manifest** — themes live under `resources/themes/{slug}/` as a `manifest.json` plus an `assets/` folder; **no Blade per theme**.
3. **Per-section decoration slot system** — `frame`, `tossed`, `scene`, `icon` are slots on every section; the manifest binds asset files to slots.
4. **Guest tokenization** — `/{slug}/{token?}` is the canonical URL; tokens resolve to per-guest greeting + pre-filled RSVP/guestbook + opens tracking.

The PoC ships **one flagship theme (`watercolor-lush`)** that visually matches sage-watercolor satumomen 70-80%, rendered end-to-end from seed data through the engine.

---

## Quick start

```powershell
composer install
npm install
copy .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan themes:publish-assets watercolor-lush
npm run build
php artisan serve
```

Then open:

- `http://localhost:8000/raka-dewi` — generic invitation cover (no greeting).
- `http://localhost:8000/raka-dewi/{token}` — personal greeting + pre-filled forms. Find a valid token via `php artisan tinker` → `App\Models\Guest::first()->token`.

---

## Architecture in one paragraph

`PublishedInvitationController` resolves an `Invitation` by slug, loads its `Section` rows + relations, fetches the `Theme` from `ThemeRegistry::find()` (filesystem-backed, reads `manifest.json`), and renders `layouts/render.blade.php`. That layout iterates the invitation's sections and dispatches each through `<x-render-section>`, which resolves the section's `type` + `variant` to a Blade file at `resources/views/sections/{type}/{variant}.blade.php`. Each section blade pulls its decorations from `<x-theme.section-decorations>` (frame/tossed/scene) and `<x-theme.section-icon>` (SVG inline / `<img>` fallback), themed via CSS variables set from the manifest's `default_palette`.

## Tests

```powershell
vendor\bin\pest --parallel
vendor\bin\pint --test
php -d memory_limit=512M vendor\bin\phpstan analyse
```

## Adding a new theme

1. Create folder `resources/themes/{slug}/` with `manifest.json` + `assets/`.
2. Drop WebP / SVG / Lottie files into `assets/`.
3. Reference them by filename in `manifest.json` under `decorations.sections.*.{frame|tossed|scene|icon}`.
4. Run `php artisan themes:publish-assets {slug}`.
5. Set the invitation's `theme_slug` to your new slug.

No Blade authoring needed. The same 8 section variants render any theme.

## What's NOT in this PoC (intentional)

- Auth / admin / wizard UI — data is seeded via `DemoInvitationSeeder`.
- Subscription / payment — out of scope for architecture validation.
- Live preview iframe + postMessage — defer.
- Tabbed layout mode + FAB stack — defer.
- Multi-theme — only `watercolor-lush` ships.

## Stack

Laravel 13.7 · Livewire 4.3 · Tailwind v4 · Pest 4 · Pint (Laravel preset, strict_types) · Larastan level 8 · MySQL or SQLite.
