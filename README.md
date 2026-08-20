# CI4-Vue Kit

![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4?style=flat-square&logo=php&logoColor=white)
![CodeIgniter](https://img.shields.io/badge/CodeIgniter-4.x-EF4223?style=flat-square&logo=codeigniter&logoColor=white)
![Shield](https://img.shields.io/badge/Shield-Auth-22c55e?style=flat-square)
![Docker](https://img.shields.io/badge/Docker-ready-2496ED?style=flat-square&logo=docker&logoColor=white)
![License](https://img.shields.io/badge/license-MIT-blue?style=flat-square)
![Status](https://img.shields.io/badge/status-stable-brightgreen?style=flat-square)
![Version](https://img.shields.io/badge/version-1.0.0-blue?style=flat-square)

> **Forked from [codeigniter4-kit](https://github.com/iskandar221201/codeigniter4-kit)** — upgraded from a server-rendered monolith to a full-stack CI4 + Vue 3 SPA. Same author, same backend DNA, different frontend architecture.

A production-grade starter kit combining **CodeIgniter 4** as a pure REST API backend with **Vue 3** as the frontend (Vite + Vue Router + Pinia + Tailwind). Shield auth · Service layer · Audit trail · SSO · PDF export · Resumable uploads · WebSocket. Clone, extend, ship in a day.

---

## Contents

- [Requirements](#requirements)
- [Docker](#docker)
- [Quick Start](#quick-start)
- [Deployment](#deployment)
- [Architecture Overview](#architecture-overview)
- [Project Structure](#project-structure)
- [Routing](#routing)
- [API Response Envelope](#api-response-envelope)
- [Filter Stack](#filter-stack)
- [Authentication Flow](#authentication-flow)
- [Web UI Layer](#web-ui-layer)
- [Logging](#logging)
- [File Uploads](#file-uploads)
- [Transformers](#transformers)
- [Audit Trail](#audit-trail)
- [SSO Layer](#sso-layer)
- [PDF Export](#pdf-export)
- [TUS Chunked Upload](#tus-chunked-upload)
- [WebSocket Server](#websocket-server)
- [How to Add a New Resource](#how-to-add-a-new-resource)
- [Server Requirements](#server-requirements)

---

## Requirements

| Dependency | Version |
|---|---|
| PHP | 8.2+ |
| Composer | latest |
| MySQL | 8.0+ |
| MariaDB | 10.5+ *(alternative)* |
| Web Server | Apache / Nginx / `php spark serve` |

---

## Docker

Run the whole stack (CI4 API + Vue SPA + MySQL) without Node, Composer, or PHP on your machine — just [Docker](https://docs.docker.com/engine/install/) with the Compose plugin.

```bash
# 1. Build & start (first run builds the image)
docker compose up -d --build

# 2. Wait until MySQL is ready
docker compose exec db mysqladmin ping -h localhost --silent --wait=30

# 3. Run migrations (CI4 + Shield + Settings + app tables)
docker compose exec app php spark migrate --all

# 4. Seed the admin user
docker compose exec app php spark db:seed AdminSeeder
```

Open `http://localhost:8080/login` — Email: `admin@example.com`, Password: `password123`.

| Service | Address |
|---|---|
| Web UI + API (Apache) | http://localhost:8080 |
| MySQL (container) | localhost:3307 · `root` / `root` / `ci4pgk` |

The image builds the Vue SPA into `public/dist` and installs PHP dependencies at
build time. It contains **no credentials** — `.env` is generated on first start
by `docker/entrypoint.sh`.

### Environment variables

| Variable | Default | Description |
|---|---|---|
| `DB_HOSTNAME` | `db` | MySQL host (the compose service name) |
| `DB_DATABASE` | `ci4pgk` | Database name |
| `DB_USERNAME` | `root` | Database user |
| `DB_PASSWORD` | `root` | Database password |

Override them at runtime instead of baking values into the image. In
`docker-compose.yml`:

```yaml
app:
  environment:
    DB_PASSWORD: ${DB_PASSWORD:-root}
```

In Dokploy, paste the same `DB_*` values in the app's environment UI. If you
change `DB_PASSWORD`, set the matching `MYSQL_ROOT_PASSWORD` on the `db` service
too.

---

## Quick Start

Click **Use this template** → **Create a new repository**, then:

```bash
# 1. Enter the project
cd my-project

# 2. Copy environment template and fill in DB credentials
cp .env.example .env

# 3. Install PHP dependencies
composer install

# 4. Install frontend dependencies and build the Vue SPA
cd frontend && npm install && npm run build && cd ..

# 5. Run all migrations (CI4 + Shield tables)
php spark migrate --all

# 6. Seed the admin user
php spark db:seed AdminSeeder

# 7. Start the development server
php spark serve

# 8. Open the web UI
open http://localhost:8080/login
# Email: admin@example.com  |  Password: password123

# 9. Verify the API
curl http://localhost:8080/api/ping
# Expected: {"status":true,"code":200,"message":"pong","data":null}
```

### Frontend dev workflow

For hot-reload while working on the Vue SPA, run two terminals:

```bash
# Terminal 1 — API + SPA server (port 8080)
php spark serve

# Terminal 2 — Vite dev server (port 5173, proxies /api to :8080)
cd frontend && npm run dev
```

Then open `http://localhost:5173`. The Vite dev server proxies `/api/*` to `http://localhost:8080` automatically.

---

## Deployment

CI4-Vue Kit supports two deployment modes depending on your infrastructure.

### Option A — Single Server (default)

The Vue SPA builds to `public/dist/` and is served directly by CI4 via `SpaController` through a catch-all route. No Node server, no separate web server config for the frontend — one VPS, one process.

```bash
# Build the frontend
cd frontend && npm run build && cd ..

# Point your web server document root to /public
# CI4 handles both API routes and SPA catch-all
```

This is the default and recommended setup for resource-constrained environments.

### Option B — Split Server

Because the backend is a pure REST API, the frontend can also be deployed independently to any static hosting (Vercel, Netlify, S3+CDN, separate Nginx).

**1. Build and deploy the frontend:**
```bash
cd frontend && npm run build
# Deploy /public/dist to your static host
```

**2. Set the API URL in `frontend/.env`:**
```
VITE_API_URL=https://api.yourdomain.com
```

**3. Update CORS in your CI4 `.env`:**
```
CORS_ALLOWED_ORIGINS=https://yourdomain.com
```

> **Note on cookie auth with split deployment:** The httpOnly cookie (`ck_token`) requires same-domain or `SameSite=None; Secure` for cross-origin. If your frontend and backend are on different domains, ensure `CORS_ALLOWED_ORIGINS` is set to an explicit origin — not `*` — since browsers reject credentialed requests with a wildcard origin.

---

## Architecture Overview

```
Request → Filter Stack → Controller → Service → Model → Database
                                           ↓
                                     Transformer
```

| Layer | Responsibility |
|---|---|
| **Vue SPA** | Client-rendered UI (Vite + Vue Router + Pinia). Calls the API — no business logic. |
| **API Controller** | Receives JSON requests, delegates to Service, returns JSON response. Never accesses a Model directly. |
| **Service** | Holds business logic, validates input, orchestrates Model calls. |
| **Transformer** | Shapes and sanitizes response payloads before they reach the API response layer. |
| **Model** | App models extend `BaseModel` (soft delete, search/dateRange scopes). Shield-based models extend `ShieldUserModel` directly. |

### Optional Layers

| Layer | Files | Notes |
|---|---|---|
| **SSO Layer** | `SSOConfig`, `JWTService`, `SSOFilter` | JWT RS256 auth for cross-app requests. Disabled by default (`SSO_ENABLED=false`). |
| **PDF Export** | `BasePdfExporter` | Abstract base for mPDF-based PDF generation. Extend per module. |
| **TUS Chunked Upload** | `TusConfig`, `TusUploader`, `TusController`, `TusCleanupCommand` | TUS protocol-based resumable uploads for large files. Requires `composer require ankitpokhrel/tus-php`. |
| **WebSocket Server** | `WsConfig`, `WsPublisher`, `WsServer`, `WsServeCommand` | Ratchet-based real-time events. CI4 publishes via internal HTTP. Disabled by default (`WS_ENABLED=false`). |

### Lifecycle Hooks

Override any of these in your Service to react to CRUD events without touching `BaseService`:

```php
protected function afterCreate(int|string $id, array $data): void
protected function afterUpdate(int|string $id, array $data): void
protected function afterDelete(int|string $id, array $oldData): void
```

Hook failures are non-blocking — they log to `log_message()` and never break the main operation.

> Note: `BaseService::update()` returns the updated entity instead of `true`. Controllers receive the fresh record directly without a second database query.

---

## Project Structure

```
app/
├── Config/
│   ├── AppConstants.php      # HTTP status codes, pagination caps, and app-wide constants
│   ├── Filters.php           # Filter aliases and route bindings
│   ├── Routing.php           # Route discovery (auto-globs app/Modules/*/Routes.php + SPA last)
│   ├── SSOConfig.php         # SSO toggle + RSA key config
│   ├── TusConfig.php         # TUS upload dir, max size, expiry
│   └── WsConfig.php          # WebSocket host, port, enabled, secret
├── Modules/                 # Domain business — one folder per module (folder by feature)
│   ├── Auth/
│   │   ├── Controllers/AuthController.php      # Login, logout, me endpoints
│   │   ├── Services/AuthService.php            # Credential verification + token issuance
│   │   ├── Transformers/AuthTransformer.php    # Shapes auth responses
│   │   └── Routes.php                          # api/auth/* routes
│   ├── Users/               # Full CRUD — reference implementation
│   │   ├── Client/UserClient.php               # Implements the module contract
│   │   ├── Config/Services.php                 # Registers UserClient
│   │   ├── Contracts/UserClientInterface.php   # Public API for other modules
│   │   ├── Controllers/UserController.php      # CRUD endpoints
│   │   ├── Models/UserModel.php                # Extends Shield's UserModel
│   │   ├── Services/UserService.php            # Internal — not directly accessible
│   │   ├── Transformers/UserTransformer.php    # Whitelist field projection
│   │   └── Routes.php                          # api/users CRUD routes
│   ├── Ping/                 # Health check — minimal module example
│   │   ├── Controllers/PingController.php      # ping (public) + protected endpoints
│   │   └── Routes.php
│   └── Upload/               # TUS chunked upload — minimal module
│       ├── Controllers/TusController.php       # TUS protocol handler
│       └── Routes.php                          # api/upload/tus routes
├── Shared/                  # Base classes & traits — not domain
│   ├── Controllers/
│   │   ├── BaseController.php          # Base for all controllers (traits wired here)
│   │   └── BaseApiController.php       # Forces JSON response, populates $apiUser
│   ├── Models/
│   │   ├── BaseModel.php               # Timestamps, soft delete, search/dateRange scopes
│   │   └── AuditLogModel.php           # Audit log persistence
│   ├── Services/
│   │   └── BaseService.php             # CRUD + pagination + validation wiring
│   ├── Transformers/
│   │   └── BaseTransformer.php         # item()/collection()/only()/except()
│   ├── Traits/
│   │   ├── ApiResponseTrait.php        # success(), error(), created(), paginate(), noContent()
│   │   ├── AuditTrailTrait.php         # auditCreate(), auditUpdate(), auditDelete(), auditRestore()
│   │   ├── LoggableTrait.php           # logInfo(), logWarning(), logError() with JSON payload
│   │   └── QueryScopesTrait.php        # search(), dateRange(), active()
│   ├── Validation/
│   │   └── BaseValidator.php           # Thin wrapper around CI4 Validation service
│   └── Exceptions/
│       ├── ServiceException.php        # General service-layer exception
│       └── ValidationException.php     # Wraps validation errors (422)
├── Controllers/
│   └── SpaController.php     # Serves the Vue SPA (frontend/dist/index.html) — not domain
├── Filters/                  # HTTP layer — flat (domain-agnostic infrastructure)
│   ├── ApiKeyFilter.php      # Validates Bearer token via Shield AccessTokens
│   ├── AuthFilter.php        # Session auth guard for web routes
│   ├── CorsFilter.php        # CORS headers + OPTIONS preflight
│   ├── JsonBodyFilter.php    # Rejects non-JSON bodies on POST/PUT/PATCH
│   └── SSOFilter.php         # JWT Bearer token verification for SSO
├── Libraries/                # Infrastructure tools — flat
│   ├── AppLogger.php         # Static facade for structured JSON logging
│   ├── BasePdfExporter.php   # Abstract base for PDF export via mPDF
│   ├── FileUploader.php      # Standardized upload handler for module files
│   ├── JWTService.php        # JWT RS256 sign and verify
│   ├── TusUploader.php       # TUS protocol server wrapper + cleanup
│   ├── WsPublisher.php       # HTTP publisher from CI4 to Ratchet server
│   ├── WsServer.php          # Ratchet WebSocket server wrapper
│   └── Storage/
│       ├── StorageDriverInterface.php   # Storage driver contract (put/delete/url)
│       ├── LocalDriver.php   # Default local filesystem storage driver
│       └── S3Driver.php      # Optional S3-compatible storage driver
└── Views/
    ├── errors/               # CI4 native error pages (fatal fallback)
    └── exports/              # PDF export templates — plain HTML, no layout

_stubs/                       # Scaffold templates — not runtime code
├── Client/StubClient.php
├── Config/Services.php
├── Contracts/StubClientInterface.php
├── Controllers/StubController.php
├── Models/StubModel.php
├── Services/StubService.php
├── Transformers/StubTransformer.php
└── Routes.php

frontend/                     # Vue 3 SPA (Vite + Vue Router + Pinia + Tailwind)
├── src/
│   ├── main.js               # App bootstrap — fetchMe() before mount, error hooks
│   ├── App.vue               # Layout switcher (default/auth/blank) + global toast
│   ├── router/index.js       # Router + auth guard — aggregates module route files
│   ├── shared/               # Cross-module code — not domain (mirror of app/Shared/)
│   │   ├── components/
│   │   │   ├── errors/       # NotFoundView, ServerErrorView
│   │   │   ├── layout/       # AppShell, AuthLayout, Sidebar, Navbar
│   │   │   └── ui/           # PageHeader, DataTable, Badge, Datepicker, Skeleton, …
│   │   ├── composables/      # useDataTable, useForm, useConfirmDialog, useTusUpload, …
│   │   ├── services/
│   │   │   └── api.js        # Axios instance — withCredentials, envelope unwrap
│   │   ├── stores/
│   │   │   └── toast.js      # Pinia global toast
│   │   └── utils/            # errorHandler, meta (page titles)
│   ├── modules/              # Mirror of app/Modules/ — one folder per feature
│   │   ├── auth/             # Login — routes, store, service, composable, view
│   │   ├── users/            # Full CRUD — routes, store, service, UserForm, views
│   │   ├── dashboard/        # routes.js + DashboardView
│   │   ├── showcase/         # routes.js + ShowcaseView
│   │   └── welcome/          # routes.js + WelcomeView
│   │   # Each module owns its own:
│   │   #   routes.js         # Vue Router routes, merged in router/index.js
│   │   #   services/*Api.js  # Axios calls for that module
│   │   #   stores/*.js       # Pinia store for that module
│   │   #   composables/      # Feature logic (e.g. useUsers)
│   │   #   components/       # Module-scoped components (e.g. UserForm)
│   │   #   views/            # Page components (lazy-loaded per route)
│   └── tests/                # Vitest unit tests (setup.js, useDataTable.test.js)
├── vite.config.js            # base /dist/, outDir ../public/dist, @/ alias
├── tailwind.config.js        # content glob + flowbite plugin
└── package.json              # npm deps + dev/build/lint/test/analyze scripts
```

> The Vue SPA builds to `public/dist/` and is served by `SpaController` via a catch-all route (single server mode).

---

## Routing

Routes are split into **per-module files** under `app/Modules/<Module>/Routes.php` instead of a single `app/Config/Routes.php`. This keeps routing organized as the app grows — one folder per feature.

### How it works

`app/Config/Routing.php` overrides `$routeFiles` and auto-discovers `app/Modules/*/Routes.php`, sorting the module files alphabetically. The SPA catch-all lives in `app/Routers/90-spa/routes.php` and is appended **last** so it never shadows an API route.

The `$routes` variable is injected into each file's scope by the framework — it is a `RouteCollection`, not a plain array.

### Adding a module

Use the scaffold command (recommended) or create the folder by hand:

```bash
php spark make:module Posts            # Controller, Model, Service, Transformer, Routes
php spark make:module Posts --contract  # + Client, Contracts, Config/Services + forward
php spark make:module Posts --minimal   # Controller + Routes only
```

The command also registers the module namespace in `app/Config/Autoload.php` (and forwards the client service in `app/Config/Services.php` for `--contract`).

Manual route file:

```php
// app/Modules/Posts/Routes.php
use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

$routes->group('api', ['filter' => 'apiKeyFilter'], static function (RouteCollection $routes): void {
    $routes->get('posts', 'App\Modules\Posts\Controllers\PostController::index');
    $routes->post('posts', 'App\Modules\Posts\Controllers\PostController::create');
});
```

Rules of thumb:

- One folder per feature/module.
- Numeric prefix controls load order; the `(.*)` catch-all always gets the highest number.
- Protected routes wrap themselves in `$routes->group('api', ['filter' => 'apiKeyFilter'], ...)`.
- Verify with `php spark routes`.

---

## API Response Envelope

All responses follow a consistent JSON structure:

**Success**
```json
{
  "status": true,
  "code": 200,
  "message": "Success",
  "data": { }
}
```

**Error / Validation**
```json
{
  "status": false,
  "code": 422,
  "message": "Validation failed",
  "errors": { "email": "The email field is required." }
}
```

**Paginated**
```json
{
  "status": true,
  "code": 200,
  "message": "Success",
  "data": [ ],
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 100,
    "total_pages": 7
  }
}
```

---

## Filter Stack

```
Request → CorsFilter → JsonBodyFilter → ApiKeyFilter / SSOFilter / AuthFilter → Controller
```

| Filter | Applied To | Purpose |
|---|---|---|
| `CorsFilter` | `api/*` (before + after) | Injects CORS headers; handles OPTIONS preflight with `204` |
| `JsonBodyFilter` | `api/*` (before) | Rejects POST/PUT/PATCH without `Content-Type: application/json`; skips TUS routes |
| `ApiKeyFilter` | `api/*` protected group | Validates Bearer token via Shield AccessTokens |
| `SSOFilter` | `api/*` protected group (opt-in) | Verifies JWT Bearer token via RS256. Pass-through when `SSO_ENABLED=false`. |
| `AuthFilter` | web routes | Checks session login; redirects to `/login` if missing |

---

## Authentication Flow

This kit uses **hybrid token authentication**:

- **Vue SPA** — the server sets an **httpOnly cookie** (`ck_token`) on login. `HttpOnly`, `SameSite=Lax`, `Path=/`, `Secure` (auto-detected). JavaScript cannot read it (XSS-proof) and it auto-attaches via `withCredentials: true`.
- **API clients** — the raw token is returned in the login response body for programmatic clients using `Authorization: Bearer <token>`. `ApiKeyFilter` checks the Bearer header first, then falls back to the cookie.

### Login

```
POST /api/auth/login
Content-Type: application/json

{ "email": "admin@example.com", "password": "password123" }
```

Response (cookie `ck_token` also set via `Set-Cookie`):

```json
{
  "status": true,
  "code": 200,
  "message": "Login berhasil",
  "data": {
    "token": "<shield-access-token>",
    "id": 1,
    "email": "admin@example.com",
    "username": "admin"
  }
}
```

### Auth bootstrap

On app boot, `main.js` calls `GET /api/auth/me` (cookie auto-attaches) before mounting. If valid, user is restored; if not, router guard redirects to `/login`.

### Logout

```
POST /api/auth/logout
```

Revokes the Shield access token server-side and expires the `ck_token` cookie.

### `api.js` behavior (axios interceptor)

| HTTP Status | Behavior |
|---|---|
| `401` on non-login routes | Set `user = null`; redirect to `/login` |
| `401` on `/api/auth/login` | Throw `{ message }` — display error in form |
| `422` | Throw `{ errors }` — mapped per-field in form |
| Other non-OK | Toast via toast store |

> **CORS note (split server):** Cookie auth cannot use `CORS_ALLOWED_ORIGINS=*`. Set explicit origins: `CORS_ALLOWED_ORIGINS=https://yourdomain.com`.

---

## Web UI Layer

Vue 3 SPA — stateless on the server. All auth state lives in an httpOnly cookie + Pinia `user` object. Served by `SpaController` via catch-all route.

### Routes (Vue Router)

| Path | View | Description |
|---|---|---|
| `/` | `WelcomeView` | Public landing page |
| `/login` | `LoginView` | Login form |
| `/dashboard` | `DashboardView` | Dashboard |
| `/users` | `UserListView` | User list (search + pagination) |
| `/users/create` | `UserCreateView` | Create user form |
| `/users/:id` | `UserDetailView` | User detail + inline edit + delete |
| `/showcase` | `ShowcaseView` | Component gallery |
| `*` | `NotFoundView` | 404 catch-all |

### Frontend commands

```bash
cd frontend
npm run dev       # Vite dev server (hot reload)
npm run build     # production build → ../public/dist
npm run lint      # ESLint
npm run test      # Vitest unit tests
npm run analyze   # bundle visualizer → stats.html
```

### UI Design System

| Element | Style |
|---|---|
| Background | `bg-white` |
| Border | `border-gray-200` |
| Primary button | `bg-gray-900 hover:bg-gray-700` |
| Secondary button | `border-gray-300 text-gray-600` |
| Input focus | `focus:ring-gray-400` |
| Danger action | `text-red-600` |
| Active nav item | `bg-gray-900 text-white` |

App name is read from `VITE_APP_NAME` (`frontend/.env`) — never hardcoded.

---

## Logging

```php
AppLogger::info('payment.success', ['amount' => 50000, 'user_id' => 12]);
AppLogger::warning('rate.limit.hit', ['ip' => $ip]);
AppLogger::error('webhook.failed', ['payload' => $raw], $exception);
```

Every entry is a structured JSON line in `writable/logs/`:

```json
{
  "timestamp": "2026-07-20T08:44:00+07:00",
  "level": "INFO",
  "action": "user.created",
  "user_id": 3,
  "ip": "127.0.0.1",
  "context": { "id": 42 }
}
```

---

## File Uploads

### Single-Request — `FileUploader`

- UUID-based filenames, structured storage under `writable/uploads/{module}/{year}/{month}/`
- Pluggable storage drivers: Local (default) or S3-compatible
- Streaming upload via `$file->move()` — zero extra memory usage
- S3 uploads retry up to 3x with exponential backoff (100ms/200ms/400ms)

### Chunked/Resumable — TUS Protocol

For files exceeding PHP's `upload_max_filesize`. TUS splits files into chunks via multiple `PATCH` requests with pause/resume support.

```bash
composer require ankitpokhrel/tus-php
```

| Method | Path | Behavior |
|---|---|---|
| `OPTIONS` | `/api/upload/tus` | Capability discovery |
| `POST` | `/api/upload/tus` | Create upload |
| `HEAD` | `/api/upload/tus/{id}` | Get current offset |
| `PATCH` | `/api/upload/tus/{id}` | Upload a chunk |
| `DELETE` | `/api/upload/tus/{id}` | Cancel upload |

Vue composable:

```vue
<script setup>
import { useTusUpload } from '@/shared/composables/useTusUpload'
const { progress, isUploading, isComplete, result, start } = useTusUpload({
  endpoint: '/api/upload/tus',
})
</script>
```

---

## Transformers

```php
class UserTransformer extends BaseTransformer
{
    public function transform(array $item): array
    {
        return $this->only($item, ['id', 'name', 'email']) + [
            'joined_at' => $item['created_at'] ?? null,
        ];
    }
}
```

Helper methods: `only(array $data, array $keys)`, `except(array $data, array $keys)`.

---

## Audit Trail

Each audit log entry stores: actor info, action, target model + record id, old/new values, request metadata (IP, user agent), timestamp.

- `auditUpdate()` only records changed fields — log stays compact.
- Non-blocking by design — failures never break request flow.

```bash
php spark migrate
```

---

## SSO Layer

Optional JWT RS256-based Single Sign-On for cross-application auth. Disabled by default (`SSO_ENABLED=false`).

```
SSO Server                    Resource Server
POST /api/auth/login          Authorization: Bearer <JWT>
    ↓                                  ↓
JWTService::sign()            JWTService::verify() — offline, no HTTP call
    ↓                                  ↓
JWT (RS256) → client          Valid → $request->ssoUser injected
```

```bash
openssl genrsa -out private.pem 2048
openssl rsa -in private.pem -pubout -out public.pem
composer require firebase/php-jwt:^7.0
```

When `SSO_ENABLED=false`, `SSOFilter` is a complete pass-through — zero overhead.

---

## PDF Export

Optional mPDF-based PDF generation.

```bash
composer require mpdf/mpdf:^8.2
```

```php
class UserPdfExporter extends BasePdfExporter
{
    protected function buildHtml(array $data): string
    {
        return view('exports/users_pdf', ['users' => $data], ['saveData' => true]);
    }
}
```

---

## WebSocket Server

Optional real-time events via Ratchet. CI4 publishes to a separate Ratchet process via internal HTTP; Ratchet broadcasts to WebSocket clients. Disabled by default (`WS_ENABLED=false`).

```
CI4 → POST /publish → Ratchet (127.0.0.1:8082) → WebSocket clients (:8081)
```

```bash
composer require cboden/ratchet:^0.4.4
php spark ws:serve
```

`WsPublisher` is auto-called from `BaseService` lifecycle hooks — no extra code needed per module. Default payload contains only `{action, id}` — no record data broadcast.

When `WS_ENABLED=false`, `WsPublisher::publish()` is a silent no-op.

---

## How to Add a New Resource

```bash
# 1. Scaffold the module (recommended)
php spark make:module Posts            # full module
php spark make:module Posts --contract  # + client/contract layer
php spark make:module Posts --minimal   # controller + routes only

# 2. Migration
php spark make:migration CreatePostsTable && php spark migrate

# 3. Wire up the service/model/transformer in app/Modules/Posts/  (see generated stubs)

# 4. Frontend module — frontend/src/modules/posts/ with its own
#    routes.js (imported in router/index.js), views/, store, and API service.
#    See frontend/src/modules/users/ for the reference FE module.
# 5. Verify routes — php spark routes
```

See `app/Modules/Users/` for the complete reference implementation (Controller, Model, Service, Transformer, Contract, Client) and `app/Modules/Ping/` for the minimal module example.

To expose a module to other modules, add a `Contracts/` interface + `Client/` implementation (or use `--contract`), then resolve via the central service: `service('userClient')` (forwarded from `app/Config/Services.php`).

---

## Server Requirements

| Extension | Notes |
|---|---|
| `intl` | Required |
| `mbstring` | Required |
| `json` | Enabled by default |
| `mysqlnd` | Required for MySQL |
| `libcurl` | Required if using `HTTP\CURLRequest` |
| `curl` | Required if using `S3Driver` |
| `openssl` | Required for SSO key pair generation |

---

## License

MIT License — open-sourced and free to use.
