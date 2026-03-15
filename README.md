# Symfony Dashboard Skeleton

Symfony 8 + EasyAdmin 5 starter template with **hexagonal architecture**, **multi-tenant organizations**, **brand skinning**, and **user preferences**.

From zero to a fully functional admin dashboard in minutes.

## Features

- **EasyAdmin 5** — CRUD controllers, inline actions, role-based badges
- **Hexagonal Architecture** — Domain / Application / Infrastructure
- **Multi-tenant** — Organizations with M2M users, admin isolation per org
- **3 Roles** — Super Admin, Admin, User with hierarchy and protections
- **Impersonate** — Admin can switch to any user in their org
- **Brand Skinning** — Host-based, CSS variables, per-brand logos and colors
- **2 Menu layouts** — Sidebar (classic) and Top Nav (horizontal menu bar)
- **4 Skins included** — `default` (sidebar, light), `jarvis` (sidebar, dark), `topnav` (horizontal, light), `watson` (horizontal, dark)
- **Sidebar collapsed mode** — Icon-only sidebar with expand on hover
- **Brand switcher** — Super admin can switch between brands in real-time
- **User Preferences** — Sidebar, content width, locale, brand override — saved per user in DB
- **2 Dashboards** — Admin (`/admin`) and User (`/dashboard`) with cross-links (if the user has both roles)
- **Landing page** — Responsive, with SVG logo
- **i18n** — English + Spanish, easily extensible
- **Login** — Form-based with CSRF, remember-me, branded

## Requirements

- PHP >= 8.4
- MySQL 8.0
- Composer

## Quick Start

```bash
# 1. Clone
git clone https://github.com/jupaygon/symfony-dashboard-skeleton.git my-dashboard
cd my-dashboard
rm -rf .git && git init

# 2. Install dependencies
composer install

# 3. Configure database
cp .env .env.local
# Edit .env.local with your DATABASE_URL

# 4. Create database and run migration
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate

# 5. Compile assets (one-shot)
php bin/console asset-map:compile
# Or use watch mode for development (recompiles on file changes):
# php bin/console app:assets:watch

# 6. Change the super admin password
php bin/console app:user:change-password superadmin@example.com
```

Open your browser and go to your project URL (e.g. `http://my-dashboard.my-dashboard.test:81`).

## Demo Users

The migration creates 3 users and 2 organizations:

| Email                    | Password           | Role             | Organizations         |
|--------------------------|--------------------|------------------|-----------------------|
| `superadmin@example.com` | `superadmin`       | ROLE_SUPER_ADMIN | All (no org needed)   |
| `admin@example.com`      | `admin`            | ROLE_ADMIN       | Acme Corp             |
| `user@example.com`       | `user`             | ROLE_USER        | Acme Corp, Globex Inc |

**Important:** Change passwords after first install:

```bash
php bin/console app:user:change-password superadmin@example.com
php bin/console app:user:change-password admin@example.com
php bin/console app:user:change-password user@example.com
```

## Architecture

```
src/
├── Application/
│   └── Service/                        # Use cases (BrandContext, UserPreferenceService)
├── Domain/
│   ├── Contract/                       # Interfaces (BrandInterface)
│   ├── Model/                          # Entities (User, Organization, UserPreference)
│   ├── Port/                           # Repository interfaces
│   └── ValueObject/                    # Value objects (Brand)
├── Infrastructure/
│   ├── Branding/                       # Brand resolver
│   ├── Command/                        # Console commands
│   ├── EventSubscriber/                # Locale, brand resolution
│   ├── Http/
│   │   ├── Api/                        # REST endpoints
│   │   └── Controller/
│   │       ├── Crud/
│   │       │   ├── Admin/              # OrganizationCrudController, UserCrudController
│   │       │   └── BaseCrudController  # Shared CRUD config
│   │       ├── Dashboard/
│   │       │   ├── AdminDashboardController
│   │       │   └── UserDashboardController
│   │       └── Trait/                  # OrgAccessTrait
│   ├── Persistence/Doctrine/           # Repository implementations
│   ├── Security/                       # SecurityController
│   ├── Translations/                   # i18n files (en, es)
│   └── Twig/                           # Twig extensions
└── Kernel.php
```

### Layer rules

- **Domain** — No dependencies on Infrastructure or Application. Pure business logic.
- **Application** — Orchestrates domain objects. Depends on Domain only (via Port interfaces).
- **Infrastructure** — Implements Port interfaces. Handles HTTP, database, security, templates.

## Roles & Permissions

|                       | Super Admin         | Admin         | User     |
|-----------------------|---------------------|---------------|----------|
| Access `/admin`       | Yes                 | Yes           | No       |
| Access `/dashboard`   | Yes                 | Yes           | Yes      |
| See all organizations | Yes                 | Own only      | Own only |
| See all users         | Yes                 | Own org only  | —        |
| See super admin users | Yes                 | No            | —        |
| Edit super admin      | Self only           | No            | —        |
| Delete super admin    | No                  | No            | —        |
| Impersonate           | Yes (any non-super) | Own org users | No       |
| Create users          | Yes                 | Yes (own org) | No       |

The super admin:
- Has access to everything without belonging to any organization
- Can edit their own profile but cannot be deleted
- Is invisible to non-super-admin users (not shown in user lists)
- Cannot be impersonated

When an admin edits a user, organizations they don't have access to are preserved silently (not removed).

## Brand Skinning

Brands allow different visual themes per hostname. Each brand has its own CSS variables, logos, and images.

### How it works

1. A request arrives → `BrandResolverSubscriber` reads the hostname
2. Looks up the hostname in `config/brands.yaml` → resolves to a brand key
3. The DashboardController loads the brand's `skin.css` + `easyadmin-overrides.css`
4. CSS variables define all colors, logos, and visual properties

### Menu layouts

Each brand defines its menu layout via the `menu` property:

| Layout    | Description                                                               | Starting point              |
|-----------|---------------------------------------------------------------------------|-----------------------------|
| `sidebar` | Classic vertical sidebar (default EA5 layout). Supports collapsed mode.   | Use `default` brand as base |
| `topnav`  | Horizontal menu bar at the top. Sidebar is hidden. Submenus as dropdowns. | Use `topnav` brand as base  |

### Included brands

| Brand     | Menu    | Theme               | Purpose                                             |
|-----------|---------|---------------------|-----------------------------------------------------|
| `default` | sidebar | EA5 vanilla (light) | Starting point for sidebar brands                   |
| `jarvis`  | sidebar | Custom dark theme   | Example of a fully themed sidebar brand             |
| `topnav`  | topnav  | EA5 vanilla (light) | Starting point for top navigation brands            |
| `watson`  | topnav  | Frost dark theme    | Example of a fully themed top navigation brand      |

To create a new sidebar brand, copy `default`. To create a new top nav brand, copy `topnav`. Then edit `skin.css` and replace the logos.

### Configuration

```yaml
# config/brands.yaml
parameters:
    brands_default: 'default'     # Fallback if no hostname matches
    brands_map:
        'dashboard.example.com': 'default'
        'dark.example.com': 'jarvis'
        'topnav.example.com': 'topnav'
    brands_defs:
        default:
            name: 'Dashboard'
            menu: sidebar
        jarvis:
            name: 'Dashboard (Dark)'
            menu: sidebar
        topnav:
            name: 'Dashboard (Top Nav)'
            menu: topnav
```

For development with worktrees:

```yaml
# config/packages/dev/brands_dev.yaml
parameters:
    brands_dev_allowed_suffixes:
        - symfony-dashboard-skeleton.test
```

### File structure per brand

```
assets/brands/<brand>/
└── css/
    └── skin.css                    # All CSS variables (colors, logos, spacing)

public/resources/brands/<brand>/
└── images/
    ├── icons/                      # Landing page icons
    └── logos/
        ├── logo.svg                # Full logo (sidebar expanded, landing, login)
        ├── logo-mobile.svg         # Mobile logo
        └── collapsed.svg           # Sidebar collapsed logo
```

### Creating a new brand

**For a sidebar brand** (classic layout):

1. Copy `assets/brands/default/` → `assets/brands/mybrand/`
2. Copy `public/resources/brands/default/` → `public/resources/brands/mybrand/`
3. Edit `skin.css` — change CSS variable values (colors, logo paths)
4. Replace logo files with your own
5. Add to `config/brands.yaml`: `mybrand: { name: 'My Brand', menu: sidebar }`
6. Compile assets: `php bin/console asset-map:compile`

**For a top nav brand** (horizontal menu):

1. Copy `assets/brands/topnav/` → `assets/brands/mybrand/`
2. Copy `public/resources/brands/topnav/` → `public/resources/brands/mybrand/`
3. Edit `skin.css` — change CSS variable values (colors, logo paths, topnav-specific vars)
4. Replace logo files with your own
5. Add to `config/brands.yaml`: `mybrand: { name: 'My Brand', menu: topnav }`
6. Compile assets: `php bin/console asset-map:compile`

The `skin.css` file is the **only file you need to edit** to change the look. All colors are CSS variables — no hardcoded values in the override CSS.

### Brand switcher (super admin)

Super admins can switch between brands in real-time from the settings dropdown (gear icon). This is stored as a user preference (`brand_override`) and overrides the host-based resolution. Select "Configured by host" to restore the default behavior.

### CSS Architecture

There are 3 types of CSS files, each with a specific role:

#### 1. `assets/brands/<brand>/css/skin.css`

The **only file with actual colors**. Defines all CSS variables (`:root { --accent: #38bdf8; ... }`). This is what you edit to change the look of a brand.

Contains variables for: sidebar colors, content area, buttons, inputs, labels, borders, switch toggle, paginator, public pages (landing, login), logo paths.

#### 2. `assets/css/easyadmin-overrides.css`

Overrides EasyAdmin 5 default styles to apply the skin. Uses only `var(--)` references — **no hardcoded colors**. Covers: sidebar, menu, user menu, search bar, CRUD tables, forms, buttons, Tom Select dropdowns, pagination, detail pages, badges, flash messages.

**Important:** This file is **NOT loaded** for the `default` brand. The `default` brand uses EasyAdmin 5 vanilla styles. Only custom brands (like `jarvis`) load this file to override EA5 defaults.

#### 3. `assets/css/public.css`

Styles for the landing page and login page. Uses only `var(--)` references — **no hardcoded colors**. Both brands load this file.

#### 4. `assets/css/sidebar-collapsed.css`

Optional. Loaded only when the user enables "Collapsed" sidebar in their preferences. Makes the sidebar narrow (icon-only) with expand-on-hover as overlay. Desktop only — mobile uses EA5 default responsive menu. Not loaded for topnav brands.

#### 5. `assets/css/topnav-layout.css`

Optional. Loaded only for brands with `menu: topnav`. Hides the sidebar, displays a horizontal menu bar at the top, repositions the content area. Includes dropdown support for submenus.

### How CSS files are loaded

| CSS file | default | jarvis | topnav | watson | Loaded by |
|----------|---------|--------|--------|--------|-----------|
| `brands/<brand>/css/skin.css` | Yes | Yes | Yes | Yes | DashboardController |
| `css/easyadmin-overrides.css` | No | Yes | No | Yes | DashboardController (custom brands) |
| `css/topnav-layout.css` | No | No | Yes | Yes | DashboardController (topnav brands) |
| `css/sidebar-collapsed.css` | If pref | If pref | No | No | DashboardController (sidebar + user pref) |
| `css/public.css` | Yes | Yes | Yes | Yes | Landing + Login templates |

The base brands (`default` and `topnav`) do NOT load `easyadmin-overrides.css` — they show EasyAdmin 5 as it comes out of the box. Custom brands (`jarvis`, `watson`) load the overrides to apply their theme. Topnav brands additionally load `topnav-layout.css` for the horizontal menu bar.

### Non-asset-mappable files

Images referenced via CSS `url()` (like logos) cannot go through AssetMapper because it renames files with hashes, breaking the CSS paths. These go in `public/resources/`:

```
public/resources/brands/<brand>/
└── images/
    ├── icons/                      # SVG icons for the landing page
    └── logos/
        ├── logo.svg                # Full logo (sidebar expanded)
        ├── logo-mobile.svg         # Mobile responsive logo
        └── collapsed.svg           # Small logo for collapsed sidebar
```

The CSS references these with absolute paths: `url('/resources/brands/jarvis/images/logos/logo.svg')`.

The override CSS files have **zero hardcoded colors** — everything references variables from `skin.css`. This means a new brand only needs to change `skin.css` and replace the images to get a completely different look.

## User Preferences

User preferences are stored in the `user_preference` table (field/value per user). They are configurable in `config/user_preferences.yaml`:

```yaml
parameters:
    user_preferences:
        sidebar_collapsed:
            type: boolean
            default: false
            label: 'Sidebar collapsed'
        content_maximized:
            type: boolean
            default: true
            label: 'Content maximized'
        locale:
            type: string
            default: 'en'
            label: 'Language'
```

Users can change their preferences from the **settings dropdown** (gear icon) in the top bar. Preferences are saved via API (`/api/user/preference/toggle`) and applied on page reload.

To add a new preference:
1. Add it to `config/user_preferences.yaml`
2. Read it with `UserPreferenceService::get($user, 'field_name')`
3. Add a toggle in the settings dropdown (`templates/bundles/EasyAdminBundle/layout.html.twig`)

## Translations

Translation files are in `src/Infrastructure/Translations/`:

```
messages+intl-icu.en.yaml
messages+intl-icu.es.yaml
```

Available languages are configured in `config/parameters.yaml`:

```yaml
parameters:
    app.languages:
        'en': { code: 'en', name: 'English' }
        'es': { code: 'es', name: 'Español' }
```

To add a new language:
1. Create `src/Infrastructure/Translations/messages+intl-icu.XX.yaml` with the same keys
2. Add the locale to `config/parameters.yaml`

The user's language preference is saved automatically when they switch languages.

## Console Commands

| Command                            | Description                            |
|------------------------------------|----------------------------------------|
| `app:user:change-password <email>` | Change a user's password interactively |
| `app:assets:watch`                 | Watch assets and recompile on changes  |

## Stack

- **Symfony 8.0** — Latest stable
- **EasyAdmin 5.0** — Admin generator
- **PHP 8.4** — Required minimum
- **Doctrine ORM 3** — Database abstraction
- **PHPUnit 11** — Testing

## License

MIT
