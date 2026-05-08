# CLAUDE.md — Marrified Studio Engineering Guidelines

> **Audience:** AI assistants and human contributors.
> Read this first. The plan file at `C:\Users\choxa\.claude\plans\saya-ingin-membuat-web-mellow-whistle.md` is the canonical scope reference.

---

## 1. Project Overview

**Marrified Studio** is an **architecture spike PoC** for a wedding-invitation engine. It is **not** a finished product — it exists to prove that 4 architectural pillars can produce satumomen-quality output:

1. **Section as first-class entity** — `App\Models\Section` rows order/type/variant/enabled.
2. **Theme = Asset Pack + Manifest** — `resources/themes/{slug}/manifest.json` + `assets/`. No Blade per theme.
3. **Per-section decoration slot system** — `frame, tossed, scene, icon` slots wired by manifest.
4. **Guest tokenization** — `/{slug}/{token?}` URL routes; token resolves to greeting + pre-fill + opens tracking.

**Hard product rules** (do not violate):
- No auth / admin / wizard UI in PoC scope.
- No payment integration ever.
- No Blade per theme — themes are data + assets only.
- Backwards-compat is irrelevant — this project has zero deployments yet.

---

## 2. Stack

| Layer | Tool | Version |
|---|---|---|
| PHP | 8.3 | (Herd) |
| Framework | Laravel | 13.7 |
| Livewire | 4.3 | wizard / forms |
| CSS | Tailwind | v4 (Vite plugin) |
| Bundler | Vite | 8 |
| DB | SQLite (PoC) / MySQL (later) | |
| Tests | Pest | 4 |
| Static | Larastan / PHPStan | level 8 |
| Format | Laravel Pint | strict_types preset |

---

## 3. Local Setup

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

Open `http://localhost:8000/raka-dewi/{token}` — `Guest::first()->token`.

---

## 4. Directory Map

```
app/
  Console/Commands/
    ThemesPublishAssets.php          # copy resources/themes/{slug}/assets → public/themes/{slug}
  Http/Controllers/
    PublishedInvitationController.php # routes /{slug}/{token?}
  Livewire/Public/
    RsvpForm.php, GuestbookForm.php  # public forms; pre-fill from $guest
  Models/
    Invitation, Couple, Event, Guest, Section,
    GuestbookMessage, Rsvp, GiftAccount
  Services/Themes/
    ThemeRegistry.php                # filesystem-backed; reads manifest.json
    Theme.php                        # readonly value object
  Support/
    ThemeAsset.php                   # asset URL helper
    GuestToken.php                   # 10-char base62 unique generator

database/
  migrations/
  factories/
  seeders/
    DatabaseSeeder.php → DemoInvitationSeeder

resources/
  css/
    app.css                          # Tailwind v4 entry
    render.css                       # public render styles
    theme-anims.css                  # decoration slot positioning
  js/
    app.js, render.js
  themes/
    watercolor-lush/
      manifest.json                  # palette, fonts, default variants, decorations
      assets/                        # WebP, SVG, Lottie
  views/
    layouts/render.blade.php         # ONE layout drives every theme
    components/
      render-section.blade.php       # type+variant → blade dispatcher
      theme/
        section-decorations.blade.php
        section-icon.blade.php
    sections/
      cover/arch.blade.php
      quotes/default.blade.php
      couple/side-by-side.blade.php
      event/card.blade.php
      gallery/grid.blade.php
      gift/cashless-modal.blade.php
      rsvp/default.blade.php
      guestbook/default.blade.php

routes/
  web.php                            # Route::get('/{slug}/{token?}', ...)

tests/
  Feature/PublishedInvitationTest.php
  Unit/ThemeRegistryTest.php, ModelRelationshipsTest.php
  Pest.php                           # binds RefreshDatabase + withoutVite()

public/themes/                       # gitignored, populated by ThemesPublishAssets
```

---

## 5. Coding Conventions

1. `declare(strict_types=1);` at the top of every PHP file (Pint enforces this).
2. `final class` for models, services, support classes (Pint won't enforce — author must).
3. Constructor property promotion + `readonly` in services / VOs.
4. Native enums for fixed-set columns (none yet — PoC uses string enums via column constraint).
5. **Theme is filesystem data**, not a DB row. Read it via `ThemeRegistry::find($slug)` — never `Theme::find()`.
6. Section variants live at `resources/views/sections/{type}/{variant}.blade.php`. Add new variant = new blade file, optionally update theme manifest's `default_section_variants` to point a theme to it.
7. Decoration components (`<x-theme.section-decorations>`, `<x-theme.section-icon>`) read `$theme->decorationFor($key)`. Don't bypass.
8. Guest token format: 10-char base62. Use `GuestToken::ensureUnique()` in production code; `GuestToken::generate()` in factories.

---

## 6. The 4-Pillar Render Flow

When a user opens `/raka-dewi/Ab3xKl9pZq`:

1. **Route** (`routes/web.php`): catches `/{slug}/{token?}`, where token regex `[A-Za-z0-9]{8,16}`.
2. **Controller** (`PublishedInvitationController`): loads `Invitation` by slug + relations, resolves `Theme` via `ThemeRegistry::find($invitation->theme_slug)`, optionally resolves `Guest` by token (incrementing `opens_count`).
3. **Layout** (`layouts/render.blade.php`): sets CSS vars from theme palette/fonts, separates cover from body, loops sections.
4. **Dispatcher** (`<x-render-section>`): for each section, tries variants in order: explicit `$section->variant` → theme's `defaultSectionVariants[$type]` → `'default'`. Falls back gracefully via `view()->exists()`.
5. **Section variant** (e.g. `sections/cover/arch.blade.php`): pulls decorations + icon, renders content. Receives `$section, $theme, $invitation, $guest`.
6. **Decoration components**: render `<img>` for frame/tossed/scene per `$theme->decorations['sections'][$key]`. Section icon inlines SVG (using `currentColor`) or falls back to `<img>`.

---

## 7. Adding a Theme

1. `resources/themes/{slug}/manifest.json` — minimum fields: `slug`, `name`, `default_palette`, `default_fonts`, `default_section_variants`, `decorations`.
2. `resources/themes/{slug}/assets/` — WebP / SVG / preview.webp.
3. `php artisan themes:publish-assets {slug}` — copies to `public/themes/{slug}/`.
4. Update `Invitation::$theme_slug` to point to the new slug.

The same 8 section variants render any theme. **No Blade authoring** — designer + manifest is enough.

---

## 8. Testing

- Pest `pest()->extend(TestCase::class)->use(RefreshDatabase::class)->beforeEach(fn() => $this->withoutVite())->in('Feature', 'Unit')` binds Laravel + skips Vite manifest in tests.
- Faker locale `en_US` set in `phpunit.xml`.
- DB connection in tests: SQLite `:memory:`.

```powershell
vendor\bin\pest --parallel
```

---

## 9. Don't

- Don't add per-theme Blade files — the engine is **section-based**, themes are data.
- Don't bypass `ThemeRegistry`; never `file_get_contents('manifest.json')` directly in views.
- Don't insert per-section data into Section's `content` JSON if a model field exists (gallery images, gift accounts have their own tables).
- Don't deploy this PoC to production — it has no auth.
