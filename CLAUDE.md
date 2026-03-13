# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Overview

This is the **Core Admin Package** (`lthn/php-admin`) - an admin panel and service layer for the Core PHP Framework. It provides the Hub dashboard, form components with authorisation, global search, Livewire modals, and honeypot security monitoring.

## Commands

```bash
php artisan serve             # Laravel dev server
npm run dev                   # Vite dev server (Tailwind v4)
npm run build                 # Production asset build
./vendor/bin/pint --dirty     # Format changed files only
./vendor/bin/pest             # Run all tests
./vendor/bin/pest --filter=SearchTest  # Run specific test
```

CI matrix: PHP 8.2, 8.3, 8.4.

## Architecture

Four Boot.php files wire up different concerns via event-driven registration:

| File | Namespace | Purpose |
|------|-----------|---------|
| `src/Boot.php` | `Core\Admin\` | Main package provider - form components (`core-forms` prefix), search registry singleton |
| `src/Website/Hub/Boot.php` | `Website\Hub\` | Admin frontend - routes, Livewire components, menu. Activates via domain pattern matching (`/^(hub\.)?core\.(test\|localhost)$/`) |
| `src/Mod/Hub/Boot.php` | `Core\Admin\Mod\Hub\` | Admin backend - models, migrations, 30+ Livewire modals. Lazy-loads on `AdminPanelBooting` event |
| `Service/Boot.php` | `Core\Service\Admin\` | Service definition for `platform_services` table |

**Key pattern — event-driven lazy loading:**
Boot classes declare a static `$listens` array mapping events to handler methods. Modules only register their components when the relevant event fires (e.g., `AdminPanelBooting`), not at application boot. This is important to understand when adding new features — registration happens in event handlers, not in `register()`/`boot()`.

```php
public static array $listens = [
    AdminPanelBooting::class => 'onAdminPanel',
    DomainResolving::class => 'onDomainResolving',
];
```

### Key Systems

**Form Components** (`src/Forms/`) - Class-backed Blade components with `core-forms` prefix and authorisation via `HasAuthorizationProps` trait:
- Props: `canGate`, `canResource`, `canHide` for permission-based disable/hide
- Authorisation only checks when BOTH `canGate` AND `canResource` are set; explicit `disabled` prop takes precedence
- 7 components: Input, Textarea, Select, Checkbox, Button, Toggle, FormGroup

**Search System** (`src/Search/`) - Provider-based search with fuzzy matching and relevance scoring:
- Implement `SearchProvider` interface: `search()`, `searchType()`, `searchLabel()`, `searchIcon()`, `getUrl()`, `searchPriority()`, `isAvailable()`
- `SearchProviderRegistry` aggregates providers, sorts by priority (lower = higher), calculates relevance scores (100=exact, 90=starts-with, 80=whole-word, 70=substring, 60=word-start, 40=fuzzy)
- Built-in `AdminPageSearchProvider` covers 13 admin pages at priority 10

**Admin Menu** - Implement `AdminMenuProvider` interface. Items have `group` (dashboard, workspaces, settings, admin), `priority`, `icon`, and optional `'admin' => true` flag. Menu registration uses closures for deferred route resolution.

**Honeypot Security** (`src/Mod/Hub/Models/HoneypotHit`) - Tracks attacks with severity levels ("warning" for robots.txt violations, "critical" for admin probing). Includes bot detection for 23+ patterns and IP/geo tracking.

**Livewire Modals** - Full-page Livewire components using `->layout('hub::admin.layouts.app')`. Routes are GET-based (Livewire handles forms internally). Routes prefixed with `/hub`, named `hub.*`.

## Conventions

- **UK English** - colour, organisation, centre (never American spellings)
- **Strict types** - `declare(strict_types=1);` in every PHP file
- **Type hints** - All parameters and return types
- **Flux Pro** - Use Flux components, not vanilla Alpine
- **Font Awesome Pro** - Use FA icons, not Heroicons
- **Pest** - Write tests using Pest `describe`/`it` syntax with `expect()` assertions
- **Immutable DTOs** - Use readonly properties; mutation methods return new instances (see `SearchResult::withTypeAndIcon()`)

## Packages

| Package | Purpose |
|---------|---------|
| `lthn/php` | Core framework, events, module discovery |
| `lthn/php-admin` | This package - admin panel, modals |
| `lthn/php-api` | REST API, scopes, rate limiting |
| `lthn/php-mcp` | Model Context Protocol for AI agents |

## License

EUPL-1.2 (copyleft) - See LICENSE for details.
