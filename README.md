# CI4-Vue Kit

![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4?style=flat-square&logo=php&logoColor=white)
![CodeIgniter](https://img.shields.io/badge/CodeIgniter-4.x-EF4223?style=flat-square&logo=codeigniter&logoColor=white)
![Shield](https://img.shields.io/badge/Shield-Auth-22c55e?style=flat-square)
![Docker](https://img.shields.io/badge/Docker-ready-2496ED?style=flat-square&logo=docker&logoColor=white)
![License](https://img.shields.io/badge/license-MIT-blue?style=flat-square)
![Status](https://img.shields.io/badge/status-stable-brightgreen?style=flat-square)
![Version](https://img.shields.io/badge/version-1.0.0-blue?style=flat-square)

> Forked from [codeigniter4-kit](https://github.com/iskandar221201/codeigniter4-kit) — same backend DNA, upgraded to a full-stack CI4 + Vue 3 SPA.

**CodeIgniter 4** as a pure REST API backend. **Vue 3** (Vite + Vue Router + Pinia + Tailwind) as the frontend. Both organized around the same folder-by-feature module structure so the frontend and backend grow together without the codebase becoming a mess.

---

## Why modular?

Most CI4 starters dump everything into `Controllers/`, `Models/`, and `Services/`. It works until the project has 10+ resources — then finding, changing, or deleting anything becomes archaeology.

This kit uses **folder-by-feature** on both sides:

```
app/Modules/Posts/          frontend/src/modules/posts/
├── Controllers/            ├── views/
├── Models/                 ├── stores/
├── Services/               ├── services/
├── Transformers/           ├── composables/
└── Routes.php              └── routes.js
```

Every module is self-contained. Adding a feature means adding a folder. Removing a feature means removing a folder. Nothing leaks across boundaries except explicit contracts.

### Module contracts

When one module needs data from another, it goes through a typed interface — not direct model access:

```php
// Other modules call this, never UserService directly
service('userClient')->find($id);
```

The `Client/` + `Contracts/` layer keeps inter-module coupling explicit and swappable.

---

## Scaffold, don't type

```bash
php spark make:module Posts            # Controller, Model, Service, Transformer, Routes
php spark make:module Posts --contract  # + Client, Contracts, Config/Services (inter-module access)
php spark make:module Posts --minimal   # Controller + Routes only
php spark make:module Posts --fe        # + Frontend module (views, store, service, composable, routes.js)
```

Flags are composable — `--fe` works alongside `--contract` and `--minimal`:

```bash
php spark make:module Posts --fe --contract   # Full BE + contract + full FE
php spark make:module Posts --fe --minimal    # Minimal BE + full FE
```

The command registers the namespace in `Autoload.php`, wires the client service in `Config/Services.php` (for `--contract`), and auto-injects the import and route spread into `frontend/src/router/index.js` (for `--fe`). No manual wiring needed on either side.

---

## Architecture

```
Request → Filter Stack → Controller → Service → Model → Database
                                           ↓
                                     Transformer
```

| Layer | Rule |
|---|---|
| Controller | Receives JSON, delegates to Service, returns JSON. Never touches a Model directly. |
| Service | All business logic lives here. Validates input, orchestrates Models. |
| Transformer | Shapes and whitelists response payloads before they hit the wire. |
| Model | Extends `BaseModel` (soft delete, search/dateRange scopes). |

### Lifecycle hooks

Override in your Service to react to CRUD events — no framework internals required:

```php
protected function afterCreate(int|string $id, array $data): void {}
protected function afterUpdate(int|string $id, array $data): void {}
protected function afterDelete(int|string $id, array $oldData): void {}
```

Hook failures are non-blocking — they log and never break the main operation. `WsPublisher` (WebSocket broadcast) and the audit trail both run here automatically.

---

## Architecture enforcement

[Deptrac](https://github.com/qossmic/deptrac) enforces module boundary rules statically.

```bash
composer analyse
```

Rules:
- Modules may NOT import from another module's internal layers (Services, Models, Controllers, Transformers).
- **Client is an implementation detail — never import it directly. Controllers talk to their own Service; cross-module access goes through `Contracts/` only. Deptrac enforces this.**
- `Shared/` and `Libraries/` are accessible from any layer.
- `Config/` acts as the DI container and is the only layer allowed to wire Contracts to concrete Clients.

Violations are reported by file and line number. New violations fail the command.
Known pre-existing violations are recorded in `deptrac.baseline.xml`.

---

## Dev workflow

```bash
# Terminal 1 — API server
php spark serve   # :8080

# Terminal 2 — Vue SPA with hot reload
cd frontend && npm run dev   # :5173, proxies /api/* to :8080
```

Open `http://localhost:5173`. Changes to `.vue` files reflect instantly; API calls proxy through without CORS config during development.

---

## Quick start

```bash
cp .env.example .env          # fill in DB credentials
composer install
cd frontend && npm install && npm run build && cd ..
php spark migrate --all
php spark db:seed AdminSeeder
php spark serve
```

Open `http://localhost:8080/login` — `admin@example.com` / `password123`.

```bash
curl http://localhost:8080/api/ping
# {"status":true,"code":200,"message":"pong","data":null}
```

---

## Docker

No PHP, Composer, or Node required on your machine.

```bash
docker compose up -d --build
docker compose exec db mysqladmin ping -h localhost --silent --wait=30
docker compose exec app php spark migrate --all
docker compose exec app php spark db:seed AdminSeeder
```

Open `http://localhost:8080/login`.

| Service | Address |
|---|---|
| Web UI + API | http://localhost:8080 |
| MySQL | localhost:3307 · `root` / `root` / `ci4pgk` |

The image builds the Vue SPA into `public/dist/` and installs PHP deps at build time. `.env` is generated by `docker/entrypoint.sh` on first start — no credentials baked into the image.

---

## Deployment

### Single server (default)

Vue SPA builds to `public/dist/`. CI4 serves it via `SpaController` through a catch-all route. One VPS, one process, no Node in production.

```bash
cd frontend && npm run build && cd ..
# Point web server document root to /public
```

### Split server

Because the backend is a pure REST API, the frontend deploys independently to any static host (Vercel, Netlify, S3+CDN).

```bash
# frontend/.env
VITE_API_URL=https://api.yourdomain.com
```

```bash
# CI4 .env
CORS_ALLOWED_ORIGINS=https://yourdomain.com
```

> Cookie auth (`ck_token`) requires `SameSite=None; Secure` across different domains. Never use `CORS_ALLOWED_ORIGINS=*` with credentialed requests — browsers reject it.

---

## Authentication

Hybrid approach: Vue SPA gets an `httpOnly` cookie (`ck_token`) — JS can't read it, XSS-proof, auto-attaches via `withCredentials: true`. API clients get the raw token in the response body for `Authorization: Bearer` usage. `ApiKeyFilter` checks the Bearer header first, falls back to the cookie.

```bash
POST /api/auth/login
{ "email": "admin@example.com", "password": "password123" }
```

```json
{
  "status": true,
  "code": 200,
  "message": "Login berhasil",
  "data": { "token": "...", "id": 1, "email": "admin@example.com", "username": "admin" }
}
```

On app boot, `main.js` calls `GET /api/auth/me` before mounting. Valid cookie → user restored. No cookie → router guard redirects to `/login`.

---

## API response envelope

All responses use the same shape:

```json
{ "status": true,  "code": 200, "message": "Success", "data": {} }
{ "status": false, "code": 422, "message": "Validation failed", "errors": {} }
{ "status": true,  "code": 200, "message": "Success", "data": [], "meta": { "current_page": 1, "per_page": 15, "total": 100, "total_pages": 7 } }
```

`api.js` unwraps the envelope automatically — components receive `data` directly.

---

## Project structure

```
app/
├── Config/
│   └── Routing.php           # Auto-discovers app/Modules/*/Routes.php; SPA catch-all last
├── Modules/                  # One folder per feature
│   ├── Auth/                 # Login, logout, me
│   ├── Users/                # Full CRUD — reference implementation
│   ├── Ping/                 # Minimal module example
│   └── Upload/               # TUS chunked upload
├── Shared/                   # Base classes, traits — not domain
│   ├── Controllers/          # BaseController, BaseApiController
│   ├── Models/               # BaseModel, AuditLogModel
│   ├── Services/             # BaseService (CRUD + pagination + validation)
│   ├── Transformers/         # BaseTransformer (item / collection / only / except)
│   └── Traits/               # ApiResponseTrait, AuditTrailTrait, LoggableTrait, QueryScopesTrait
├── Filters/                  # CorsFilter, ApiKeyFilter, JsonBodyFilter, SSOFilter, AuthFilter
└── Libraries/                # AppLogger, FileUploader, JWTService, TusUploader, WsPublisher, …

frontend/src/
├── modules/                  # Mirror of app/Modules/
│   ├── auth/
│   ├── users/                # Full CRUD — reference implementation
│   ├── dashboard/
│   └── showcase/             # Component gallery
└── shared/
    ├── components/ui/        # DataTable, Badge, PageHeader, Skeleton, …
    ├── composables/          # useDataTable, useForm, useConfirmDialog, useTusUpload
    └── services/api.js       # Axios — withCredentials, envelope unwrap, 401/422 handling

_stubs/                       # Scaffold templates used by make:module
├── Controllers/              # BE stubs
├── Models/
├── Services/
├── Transformers/
├── Client/
├── Contracts/
├── Config/
├── Routes.php
└── fe/                       # FE stubs (used by --fe flag)
    ├── routes.js
    ├── services/
    ├── stores/
    ├── composables/
    └── views/
```

---

## Adding a resource

```bash
# 1. Scaffold
php spark make:module Posts --contract --fe

# 2. Migration
php spark make:migration CreatePostsTable && php spark migrate

# 3. Implement the generated stubs in app/Modules/Posts/

# 4. Customize the generated frontend in frontend/src/modules/posts/
#    ListView, store, service, and route are ready — adjust columns and fields

# 5. Verify
php spark routes
```

Reference implementations: `app/Modules/Users/` (full CRUD + contract), `app/Modules/Ping/` (minimal).

---

## Optional layers

All disabled by default — zero overhead when off.

| Feature | Enable | Notes |
|---|---|---|
| **SSO** | `SSO_ENABLED=true` + RSA key pair | JWT RS256. `SSOFilter` is a complete pass-through when off. |
| **WebSocket** | `WS_ENABLED=true` | Ratchet. `WsPublisher::publish()` is a silent no-op when off. |
| **PDF Export** | `composer require mpdf/mpdf:^8.2` | Extend `BasePdfExporter` per module. |
| **TUS Uploads** | `composer require ankitpokhrel/tus-php` | Chunked resumable uploads. `useTusUpload` composable included. |
| **S3 Storage** | Configure `S3Driver` | Swap via `StorageDriverInterface`. Local driver is default. |
| **Audit Trail** | Included, always on | Runs in `BaseService` hooks. Logs actor, action, old/new values, IP. |

---

## Frontend commands

```bash
cd frontend
npm run dev       # Vite dev server (hot reload, proxies /api to :8080)
npm run build     # Production build → ../public/dist
npm run test      # Vitest unit tests
npm run lint      # ESLint
npm run analyze   # Bundle visualizer → stats.html
```

---

## Server requirements

| | |
|---|---|
| PHP | 8.2+ |
| Extensions | `intl`, `mbstring`, `json`, `mysqlnd`, `libcurl` |
| Database | MySQL 8.0+ or MariaDB 10.5+ |
| Optional | `openssl` (SSO), `curl` (S3Driver) |

---

## AI Prompt Templates (HITL)

This kit includes the `.agents/hitl-rule` folder, which contains a collection of Human-in-the-Loop (HITL) prompt templates designed to assist AI agents (like GitHub Copilot, Claude, or other coding assistants) in understanding the strict architectural rules and standards of this project.

These templates are maintained by the author of this kit and are part of the broader [hitl-work](https://github.com/iskandar221201/hitl-work) repository.

---

## License

MIT
