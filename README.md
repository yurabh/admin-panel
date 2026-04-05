# Admin Panel API

A production-ready REST API backend for a content management admin panel, built with **Laravel 11** and **PHP 8.2**. Provides a full-featured JSON API for managing blog posts, static pages, categories, tags, comments, system settings, users with role-based access control, and Stripe-powered subscription billing.

---

## Table of Contents

- [Tech Stack](#tech-stack)
- [Architecture](#architecture)
- [Getting Started](#getting-started)
- [Environment Variables](#environment-variables)
- [Makefile Commands](#makefile-commands)
- [API Documentation (Swagger)](#api-documentation-swagger)
- [API Endpoints](#api-endpoints)
- [Authentication](#authentication)
- [User Roles & Authorization](#user-roles--authorization)
- [Billing & Subscriptions (Stripe)](#billing--subscriptions-stripe)
- [Background Jobs & Queues](#background-jobs--queues)
- [Caching Strategy](#caching-strategy)
- [File Storage (AWS S3)](#file-storage-aws-s3)
- [Artisan Commands](#artisan-commands)
- [Testing](#testing)
- [Project Structure](#project-structure)
- [Data Models & Relationships](#data-models--relationships)
- [Architectural Patterns](#architectural-patterns)
- [CI/CD Pipeline](#cicd-pipeline)
- [Docker Infrastructure](#docker-infrastructure)

---

## Tech Stack

### Core

| Component | Technology | Version |
|---|---|---|
| Language | PHP | 8.2 |
| Framework | Laravel | 11.x |
| Database | MySQL | 8.0 |
| Cache & Queues | Redis | 7.x |
| Authentication | Laravel Sanctum | 4.3 |
| Billing | Laravel Cashier (Stripe) | 16.5 |
| Queue Monitoring | Laravel Horizon | 5.45 |
| File Storage | AWS S3 (Flysystem) | 3.0 |
| PDF Generation | DomPDF | 3.1 |
| API Documentation | L5-Swagger (OpenAPI 3) | 10.1 |
| Redis Client | Predis | 3.4 |

### Infrastructure

| Component | Technology |
|---|---|
| Containerization | Docker + Docker Compose |
| Web Server | Nginx |
| CI/CD | GitLab CI/CD |
| Deployment | AWS ECS (Fargate) |
| Container Registry | Docker Hub |

### Development Tools

| Tool | Purpose |
|---|---|
| Laravel Pint | Code style (PHP-CS-Fixer) |
| Laravel Pail | Real-time log viewer |
| Laravel IDE Helper | IDE autocompletion |
| Xdebug | PHP debugging |
| PHPUnit 11 | Testing framework |
| Mockery | Mock objects |
| FakerPHP | Test data generation |

---

## Architecture

### Request Flow

```
HTTP Request
    → Route (routes/api.php)
        → Middleware (auth:sanctum, admin, subscribed)
            → Controller (thin — delegates to action)
                → FormRequest (input validation)
                → Action (business logic)
                    → Model (Eloquent ORM)
                    → Event (optional side-effects)
                → Resource (JSON response transformation)
    → HTTP Response (JSON)
```

### Key Design Decisions

- **Action Pattern** — all business logic lives in dedicated single-responsibility `Action` classes under `app/Actions/`, keeping controllers thin
- **API Resources** — all JSON responses are transformed through Laravel API Resources for consistent output format
- **Form Requests** — input validation is handled in dedicated request classes, separate from controllers
- **Policy-based Authorization** — model-level access control via Laravel Policies
- **Event-driven Side-effects** — notifications and post-processing triggered through Events & Listeners
- **Cache with Auto-invalidation** — system settings cached indefinitely with automatic invalidation via Model Observer
- **Job Batching** — bulk notifications dispatched using `Bus::batch()` for efficient queue processing

---

## Getting Started

### Prerequisites

- Docker & Docker Compose
- Make (optional, for convenience commands)

### Quick Start with Docker

```bash
# 1. Clone the repository
git clone <repo-url>
cd admin-panel-main

# 2. Copy and configure environment variables
cp .env.example .env

# 3. Update .env with Docker settings:
#    DB_CONNECTION=mysql
#    DB_HOST=db
#    REDIS_HOST=redis
#    CACHE_STORE=redis
#    QUEUE_CONNECTION=redis

# 4. Build and start containers
make build
make up

# 5. Install dependencies (if needed)
docker compose exec app composer install

# 6. Generate application key
docker compose exec app php artisan key:generate

# 7. Run migrations and seed the database
make migrate
make seed

# 8. Generate Swagger documentation
docker compose exec app php artisan l5-swagger:generate
```

The application will be available at:
- **API:** `http://localhost:8080`
- **Swagger UI:** `http://localhost:8080/api/documentation`
- **Horizon Dashboard:** `http://localhost:8080/horizon`

### Local Development (without Docker)

```bash
# Requirements: PHP 8.2, Composer, Node.js, MySQL/SQLite, Redis

# 1. Install dependencies
composer install
npm install

# 2. Configure environment
cp .env.example .env
php artisan key:generate

# 3. Run migrations with seed data
php artisan migrate --seed

# 4. Start all development services simultaneously
composer dev
# This starts: Laravel server + Queue worker + Pail log viewer + Vite dev server
```

---

## Environment Variables

### Application

```env
APP_NAME=Laravel
APP_ENV=local                    # local | production | testing
APP_KEY=                         # Generated via: php artisan key:generate
APP_DEBUG=true                   # Set to false in production
APP_TIMEZONE=UTC
APP_URL=http://localhost
APP_FRONTEND_URL=http://localhost:3000   # Frontend app URL (used for Stripe redirects)
```

### Database

```env
# SQLite (default for local dev)
DB_CONNECTION=sqlite

# MySQL (Docker / production)
DB_CONNECTION=mysql
DB_HOST=db                       # 'db' for Docker, '127.0.0.1' for local
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=laravel
DB_PASSWORD=secret
```

### Redis

```env
REDIS_HOST=redis                 # 'redis' for Docker, '127.0.0.1' for local
REDIS_PORT=6379
REDIS_PASSWORD=null
```

### Cache, Queues, Sessions

```env
CACHE_STORE=redis                # redis | database | array
QUEUE_CONNECTION=redis           # redis | database | sync
SESSION_DRIVER=database
```

### Mail

```env
MAIL_MAILER=smtp                 # smtp | log | array
MAIL_HOST=smtp.provider.com
MAIL_PORT=587
MAIL_USERNAME=your-username
MAIL_PASSWORD=your-password
MAIL_FROM_ADDRESS=noreply@example.com
MAIL_FROM_NAME="${APP_NAME}"
```

### AWS S3 (File Storage)

```env
AWS_ACCESS_KEY_ID=your-key
AWS_SECRET_ACCESS_KEY=your-secret
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=your-bucket
```

### Stripe (Billing)

```env
STRIPE_KEY=pk_live_xxx
STRIPE_SECRET=sk_live_xxx
STRIPE_WEBHOOK_SECRET=whsec_xxx
CASHIER_CURRENCY=usd
```

### Sanctum

```env
SANCTUM_STATEFUL_DOMAINS=localhost,127.0.0.1:8000
SANCTUM_TOKEN_PREFIX=
```

---

## Makefile Commands

| Command | Description |
|---|---|
| `make help` | Show all available commands |
| `make up` | Start all containers in detached mode |
| `make down` | Stop and remove all containers |
| `make restart` | Restart all Docker services |
| `make build` | Rebuild Docker images without cache |
| `make shell` | Open a terminal inside the app container |
| `make migrate` | Run pending database migrations |
| `make migrate-step` | Rollback the last migration and re-run it |
| `make seed` | Populate the database with seed data |
| `make fresh` | Wipe the database and run all migrations with seeds |
| `make test` | Run the full test suite |
| `make cache` | Clear all Laravel caches (config, route, view, cache) |
| `make tinker` | Open the interactive Artisan Tinker shell |
| `make xdebug-status` | Check Xdebug status inside the app container |

---

## API Documentation (Swagger)

The project uses **L5-Swagger** with **PHP 8 Attributes** to auto-generate OpenAPI 3 documentation directly from the codebase.

### Access

```
http://localhost:8080/api/documentation
```

### Regenerate Documentation

```bash
docker compose exec app php artisan l5-swagger:generate
```

OpenAPI attributes (`#[OAT\...]`) are placed on controllers, models, and request classes, ensuring documentation stays in sync with the code.

---

## API Endpoints

### Public Endpoints (No Authentication Required)

| Method | URL | Description |
|---|---|---|
| `POST` | `/api/login` | Authenticate and receive a Bearer token |
| `POST` | `/api/register` | Register a new user account |
| `POST` | `/api/forgot/password` | Request a password reset email |
| `POST` | `/api/password/reset` | Reset password using a token |
| `POST` | `/api/stripe/webhook` | Stripe webhook handler (Cashier) |
| `GET` | `/api/subscription/success` | Post-payment success callback |

### Authenticated Endpoints (Require `Bearer` Token)

| Method | URL | Description |
|---|---|---|
| `DELETE` | `/api/logout` | Revoke the current access token |
| `GET` | `/api/users` | List all users |
| `POST` | `/api/users` | Create a new user |
| `GET` | `/api/users/{user}` | Get user details |
| `PUT` | `/api/users/{user}` | Update a user |
| `DELETE` | `/api/users/{user}` | Delete a user |
| `GET` | `/api/categories` | List categories (requires active subscription) |
| `POST` | `/api/comments` | Create a comment |
| `GET` | `/api/comments` | List all comments |
| `GET` | `/api/comments/{comment}` | Get comment details |
| `PUT` | `/api/comments/{comment}` | Update a comment |
| `DELETE` | `/api/comments/{comment}` | Delete a comment |
| `POST` | `/api/subscribe` | Create a Stripe Checkout session |
| `POST` | `/api/subscription/cancel` | Cancel active subscription |
| `GET` | `/api/subscription/portal` | Get Stripe Billing Portal URL |
| `POST` | `/api/subscription/start-trial` | Start a free trial (5 days, no card) |
| `GET` | `/api/billing/info` | Get billing information |
| `GET` | `/api/billing/invoices` | List all invoices |
| `GET` | `/api/billing/invoices/download` | Download invoice as PDF |

### Admin Endpoints (Require `admin` Role)

#### Posts

| Method | URL | Description |
|---|---|---|
| `GET` | `/api/admin/posts` | List all posts |
| `POST` | `/api/admin/posts` | Create a new post |
| `GET` | `/api/admin/posts/{post}` | Get post details |
| `PUT` | `/api/admin/posts/{post}` | Update a post |
| `DELETE` | `/api/admin/posts/{post}` | Delete a post |
| `GET` | `/api/admin/posts/search?term=` | Search posts by title/content |
| `GET` | `/api/admin/posts/filter` | Filter posts (category, date, status) |
| `GET` | `/api/admin/posts/sorted-by-date` | Get posts sorted by publish date |
| `GET` | `/api/admin/posts/category/{categoryId}` | Get posts by category |

#### Pages

| Method | URL | Description |
|---|---|---|
| `POST` | `/api/admin/pages` | Create a new page (supports image upload) |
| `GET` | `/api/admin/pages/{page}` | Get page details |
| `PUT` | `/api/admin/pages/{page}` | Update a page |
| `DELETE` | `/api/admin/pages/{page}` | Delete a page (also removes S3 image) |

#### Settings

| Method | URL | Description |
|---|---|---|
| `GET` | `/api/admin/settings` | List all settings (cached) |
| `POST` | `/api/admin/settings` | Create a new setting |
| `GET` | `/api/admin/settings/{setting}` | Get a setting by key |
| `PUT` | `/api/admin/settings/{setting}` | Update a setting (notifies all users) |
| `DELETE` | `/api/admin/settings/{setting}` | Delete a setting |

#### Categories

| Method | URL | Description |
|---|---|---|
| `POST` | `/api/admin/categories` | Create a category |
| `GET` | `/api/admin/categories/{category}` | Get category details |
| `PUT` | `/api/admin/categories/{category}` | Update a category |
| `DELETE` | `/api/admin/categories/{category}` | Delete a category |

#### Tags

| Method | URL | Description |
|---|---|---|
| `GET` | `/api/admin/tags` | List all tags (with post count) |
| `POST` | `/api/admin/tags` | Create a tag |
| `GET` | `/api/admin/tags/{tag}` | Get tag details |
| `PUT` | `/api/admin/tags/{tag}` | Update a tag |
| `DELETE` | `/api/admin/tags/{tag}` | Delete a tag |

---

## Authentication

The API uses **Laravel Sanctum** with Bearer token authentication.

### Login

```bash
curl -X POST http://localhost:8080/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@example.com",
    "password": "password"
  }'
```

**Response:**

```json
{
  "data": {
    "access_token": "1|abc123def456...",
    "token_type": "Bearer",
    "user": {
      "id": 1,
      "name": "Admin",
      "email": "admin@example.com",
      "role": "admin"
    }
  }
}
```

### Using the Token

Include the token in the `Authorization` header for all protected requests:

```bash
curl -X GET http://localhost:8080/api/users \
  -H "Authorization: Bearer 1|abc123def456..."
```

### Register

```bash
curl -X POST http://localhost:8080/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "SecurePass123!",
    "password_confirmation": "SecurePass123!"
  }'
```

### Logout

```bash
curl -X DELETE http://localhost:8080/api/logout \
  -H "Authorization: Bearer 1|abc123def456..."
```

### Password Reset Flow

```bash
# 1. Request a password reset link
curl -X POST http://localhost:8080/api/forgot/password \
  -H "Content-Type: application/json" \
  -d '{"email": "john@example.com"}'

# 2. Reset the password using the token from the email
curl -X POST http://localhost:8080/api/password/reset \
  -H "Content-Type: application/json" \
  -d '{
    "token": "reset-token-from-email",
    "email": "john@example.com",
    "password": "NewPassword123!",
    "password_confirmation": "NewPassword123!"
  }'
```

---

## User Roles & Authorization

### Roles

| Role | Enum Value | Access Level |
|---|---|---|
| **Admin** | `admin` | Full access to all resources, including `/api/admin/*` endpoints |
| **Editor** | `editor` | Access to own posts and comments |
| **User** | `user` | Basic access: manage own comments |

### Policy-based Authorization

Access control is enforced at the model level using Laravel Policies:

| Policy | Rules |
|---|---|
| **PostPolicy** | Admins can update/delete any post; others only their own |
| **CommentPolicy** | Admins can update/delete any comment; others only their own |
| **PagePolicy** | Only the page author can update |
| **UserPolicy** | Users can update their own profile; admins can update anyone |

### Middleware

| Middleware | Alias | Description |
|---|---|---|
| `IsAdminMiddleware` | `admin` | Checks if the authenticated user has the `admin` role. Returns 403 if not. Applied to all `/api/admin/*` routes. |
| `CheckSubscriptionMiddleware` | `subscribed` | Checks if the user has an active Stripe subscription via Laravel Cashier. Returns a structured 403 with `error: subscription_required` and `action: redirect_to_pricing` for frontend handling. |

---

## Billing & Subscriptions (Stripe)

The project integrates with **Stripe** via **Laravel Cashier** for subscription management.

### Subscription Flow

```
1. Client calls POST /api/subscribe with { "price_id": "price_xxx" }
   → Backend creates a Stripe Checkout Session
   → Returns the Checkout URL

2. User is redirected to Stripe, completes payment

3. Stripe sends a webhook → POST /api/stripe/webhook
   → Cashier's WebhookController processes the event
   → Subscription is activated in the database

4. Client calls GET /api/subscription/success?session_id=cs_xxx
   → Backend verifies payment and updates the default payment method
```

### Free Trial

```bash
# Start a 5-day free trial (no card required, one-time only)
curl -X POST http://localhost:8080/api/subscription/start-trial \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{"price_id": "price_xxx"}'
```

### Cancel Subscription

```bash
# Cancel at end of billing period (access remains until ends_at)
curl -X POST http://localhost:8080/api/subscription/cancel \
  -H "Authorization: Bearer {token}"
```

### Billing Portal

```bash
# Get a URL to Stripe's self-service Billing Portal
curl -X GET http://localhost:8080/api/subscription/portal \
  -H "Authorization: Bearer {token}"
```

### Invoice Download

```bash
# Download a PDF invoice
curl -X GET "http://localhost:8080/api/billing/invoices/download?invoice_id=in_xxx" \
  -H "Authorization: Bearer {token}" \
  --output invoice.pdf
```

---

## Background Jobs & Queues

The project uses **Redis queues** with **Laravel Horizon** for monitoring.

### Queue Dashboard

```
http://localhost:8080/horizon
```

Access is restricted to authorized email addresses configured in `HorizonServiceProvider`.

### Starting Horizon

```bash
# Inside the Docker container
docker compose exec app php artisan horizon

# Or via the dev script (included in `composer dev`)
```

### Registered Jobs

| Job | Trigger | Description |
|---|---|---|
| `NotifyAdminsAboutLoginJob` | After every successful login | Notifies all other admins via email about the login event. Uses `chunkById` for memory-safe processing. Retry: 2 attempts with 10s backoff. |
| `NotifyUserAboutSettingsChangeJob` | After a system setting is updated | Sends `SettingChangedNotification` to all verified users using `Bus::batch()` for efficient batch dispatching. |

### Event-Listener Pairs

| Event | Listener | Queue | Description |
|---|---|---|---|
| `RegistrationEvent` | `RegistrationListener` | Yes | Sends email verification notification to the newly registered user |
| `NewCommentEvent` | `NotifyPostAuthorListener` | Yes | Notifies the post author about a new comment (if commenter is a different user) |

---

## Caching Strategy

### Settings Cache

All system settings are cached indefinitely using `Cache::rememberForever()`:

```php
// Retrieve all settings (from cache or DB)
Setting::getAllCached();  // Returns ['key' => 'value', ...]

// Get a specific setting
Setting::getValue('site_name', 'Default');
```

### Automatic Cache Invalidation

The `SettingObserver` automatically clears the cache when any setting is created, updated, or deleted:

```php
// SettingObserver
public function saved(Setting $setting): void
{
    Cache::forget('settings.all');
}

public function deleted(Setting $setting): void
{
    Cache::forget('settings.all');
}
```

This is registered in `AppServiceProvider::boot()`.

---

## File Storage (AWS S3)

Page images are uploaded to **AWS S3** when creating or updating pages.

### Upload

Images are stored under the `pages/images/` prefix on S3:

```bash
# Create a page with an image
curl -X POST http://localhost:8080/api/admin/pages \
  -H "Authorization: Bearer {token}" \
  -F "title=About Us" \
  -F "slug=about-us" \
  -F "content=<p>About page content</p>" \
  -F "user_id=1" \
  -F "is_published=1" \
  -F "image=@/path/to/image.jpg"
```

### URL Generation

The `Page` model computes a public S3 URL via an accessor:

```php
$page->image_url  // https://your-bucket.s3.amazonaws.com/pages/images/filename.jpg
```

### Cleanup

When a page is deleted, its associated S3 image is also removed.

---

## Artisan Commands

### `app:set-role {user} {role}`

Change a user's role.

```bash
php artisan app:set-role 1 admin     # Make user #1 an admin
php artisan app:set-role 5 editor    # Make user #5 an editor
php artisan app:set-role 3 user      # Make user #3 a regular user
```

### `app:publish-posts`

Publish all scheduled posts where `published_at <= now()` and `is_published = false`.

```bash
php artisan app:publish-posts
# [2026-04-05 12:00:00] Posts successfully published: 3
```

### `app:clean-old-comments`

Delete unapproved comments (`is_approved = false`) older than 30 days.

```bash
php artisan app:clean-old-comments
# Old Comments have been cleaned
```

---

## Testing

### Configuration

- **Framework:** PHPUnit 11
- **Database:** SQLite in-memory (configured in `phpunit.xml`)
- **Mocking:** Mockery
- **Data:** FakerPHP + Laravel Factories

### Running Tests

```bash
# Via Make
make test

# Via Docker
docker compose exec app php artisan test

# With verbose output
docker compose exec app php artisan test --verbose

# Run a specific test class
docker compose exec app php artisan test --filter=LoginTest

# Run a specific test method
docker compose exec app php artisan test --filter=test_login_successfully
```

### Test Coverage

| Module | Feature Tests | Unit Tests |
|---|---|---|
| Auth (Login) | `LoginTest` (4 tests) | — |
| Posts | `PostTest` (3 tests) | — |
| Comments | `CommentTest` (4 tests) | — |
| Settings | `SettingTest` (4 tests) | — |
| Users | `UserTest` (2 tests) | — |
| Pages | — | `ShowPageActionTest`, `DeletePageControllerTest` |
| Categories | `CategoryUpdateActionTest` | `CategoryResourceTest` |
| Comments | — | `CommentResourceTest` |
| Policies | — | `UserUpdateTest` |

### Testing Practices Used

- `RefreshDatabase` trait for test isolation
- `Queue::fake()` to verify job dispatching without execution
- `Event::fake()` to verify event dispatching
- `Storage::fake('s3')` to test S3 uploads without real AWS
- `Log::spy()` / `Log::shouldReceive()` for log assertions
- `#[Test]` PHP 8 attribute for test method identification
- `assertDatabaseHas` / `assertDatabaseMissing` for database state verification
- `actingAs($user)` for authenticated test requests

---

## Project Structure

```
admin-panel-main/
├── app/
│   ├── Actions/                    # Business logic (Action Pattern)
│   │   ├── Auth/                   #   Login, Register, Logout, ResetPassword, CreateToken
│   │   ├── Category/               #   Create, Update
│   │   ├── Comment/                #   Create, Update
│   │   ├── Page/                   #   Show, Store, Update (S3 image handling)
│   │   ├── Post/                   #   Create, Update, Search, Sort, Filter, ByCategory
│   │   ├── Setting/                #   Create, Update (batch notifications), Delete
│   │   ├── Tag/                    #   Create, Update
│   │   └── User/                   #   Update
│   │
│   ├── Console/Commands/           # Custom Artisan CLI commands
│   │   ├── ChangeUserRole.php      #   app:set-role {user} {role}
│   │   ├── CleanOldComments.php    #   app:clean-old-comments
│   │   └── PublishScheduledPosts.php   app:publish-posts
│   │
│   ├── Docker/                     # Docker configuration
│   │   ├── Dockerfile              #   Multi-stage (development → deploy)
│   │   ├── nginx/                  #   Nginx config + Dockerfile
│   │   └── php/xdebug.ini          #   Xdebug configuration
│   │
│   ├── Enums/
│   │   └── UserRole.php            # ADMIN | EDITOR | USER
│   │
│   ├── Events/                     # Domain events
│   │   ├── NewCommentEvent.php
│   │   └── RegistrationEvent.php
│   │
│   ├── Exceptions/                 # Custom exceptions with JSON rendering
│   │   ├── BillingException.php
│   │   ├── PageException.php
│   │   └── PostException.php
│   │
│   ├── Http/
│   │   ├── Controllers/            # Thin controllers (grouped by domain)
│   │   │   ├── Auth/               #   5 controllers (login, register, logout, etc.)
│   │   │   ├── Billing/            #   BillingController, SubscriptionController
│   │   │   ├── Category/
│   │   │   ├── Comment/
│   │   │   ├── Page/               #   4 single-action controllers
│   │   │   ├── Post/               #   Resource controller + search/filter
│   │   │   ├── Setting/
│   │   │   ├── Tag/
│   │   │   └── User/
│   │   ├── Middleware/
│   │   │   ├── IsAdminMiddleware.php
│   │   │   └── CheckSubscriptionMiddleware.php
│   │   ├── Requests/               # Form Request validation classes
│   │   └── Resources/              # API Resource transformers
│   │
│   ├── Jobs/                       # Queued background jobs
│   ├── Listeners/                  # Event listeners (queued)
│   ├── Mail/                       # Mailable classes
│   ├── Models/                     # Eloquent models (7 models)
│   ├── Notifications/              # Notification classes (mail channel)
│   ├── Observers/                  # Model observers (cache invalidation)
│   ├── Policies/                   # Authorization policies
│   ├── Providers/                  # Service providers
│   └── Services/                   # Business services
│       ├── BillingService.php      #   Invoice PDF download
│       └── SubscriptionService.php #   Stripe subscription lifecycle
│
├── database/
│   ├── factories/                  # Model factories (6 factories)
│   ├── migrations/                 # Database migrations (16 files)
│   └── seeders/                    # Database seeders (7 seeders)
│
├── routes/
│   ├── api.php                     # All API routes
│   ├── web.php                     # Email verification route
│   └── console.php                 # Console routes
│
├── tests/
│   ├── Feature/                    # Integration tests
│   └── Unit/                       # Unit tests
│
├── .env.example                    # Environment variable template
├── .gitlab-ci.yml                  # CI/CD pipeline configuration
├── composer.json                   # PHP dependencies
├── docker-compose.yml              # Docker services configuration
├── Makefile                        # Development convenience commands
├── phpunit.xml                     # PHPUnit configuration
└── vite.config.js                  # Vite build configuration
```

---

## Data Models & Relationships

### Entity Relationship Diagram

```
┌─────────────┐       ┌──────────────┐       ┌──────────────┐
│   users     │       │  categories  │       │    tags       │
├─────────────┤       ├──────────────┤       ├──────────────┤
│ id          │       │ id           │       │ id           │
│ name        │       │ name         │       │ name         │
│ email       │       │ slug         │       │ slug         │
│ password    │       └──────┬───────┘       │ description  │
│ role (enum) │              │               │ is_active    │
│ stripe_id   │              │               └──────┬───────┘
│ ...         │     ┌────────┴────────┐    ┌────────┴────────┐
└──────┬──────┘     │     posts       │    │    post_tag     │
       │            ├─────────────────┤    │   (pivot)       │
       │            │ id              │    ├─────────────────┤
       ├──1:N──────→│ user_id (FK)    │←──→│ post_id (FK)    │
       │            │ category_id(FK) │    │ tag_id (FK)     │
       │            │ title, slug     │    └─────────────────┘
       │            │ content         │
       │            │ is_published    │
       │            │ published_at    │
       │            └────────┬────────┘
       │                     │
       │            ┌────────┴────────┐
       │            │   comments      │
       ├──1:N──────→├─────────────────┤
       │            │ id              │
       │            │ user_id (FK)    │
       │            │ post_id (FK)    │
       │            │ content         │
       │            │ is_approved     │
       │            └─────────────────┘
       │
       │            ┌─────────────────┐
       ├──1:N──────→│    pages        │
       │            ├─────────────────┤
       │            │ id              │
       │            │ user_id (FK)    │
       │            │ title, slug     │
       │            │ content         │
       │            │ is_published    │
       │            │ image (S3 path) │
       │            └─────────────────┘
       │
       │            ┌─────────────────┐
       └──1:N──────→│  subscriptions  │
                    ├─────────────────┤
                    │ user_id         │
                    │ stripe_id       │
                    │ stripe_status   │
                    │ ends_at         │
                    └─────────────────┘

                    ┌─────────────────┐
                    │   settings      │
                    ├─────────────────┤
                    │ id              │
                    │ key (unique)    │
                    │ value           │
                    └─────────────────┘
```

### Models Summary

| Model | Key Fields | Relationships | Special Features |
|---|---|---|---|
| **User** | name, email, password, role | hasMany: Post, Comment, Page | Sanctum tokens, Cashier Billable, role cast to `UserRole` enum |
| **Post** | title, slug, content, is_published, published_at | belongsTo: User, Category; hasMany: Comment; belongsToMany: Tag | Scopes: `recent`, `search`, `byCategory`, `filter` |
| **Category** | name, slug | hasMany: Post | — |
| **Tag** | name, slug, description, is_active | belongsToMany: Post | — |
| **Comment** | content, is_approved | belongsTo: User, Post | Moderation via `is_approved` flag |
| **Page** | title, slug, content, is_published, image | belongsTo: User | S3 image URL computed via `getImageUrlAttribute()` accessor |
| **Setting** | key, value | — | `getAllCached()` with `rememberForever`, `getValue(key, default)` |

---

## Architectural Patterns

| Pattern | Where | Description |
|---|---|---|
| **Action Pattern** | `app/Actions/` | All business logic extracted into single-responsibility action classes, injected into controller methods via Laravel DI |
| **Single Action Controller** | `app/Http/Controllers/Page/` | One controller per operation (`StorePageController`, `ShowPageController`, etc.) using `__invoke()` |
| **Resource Controller** | Post, User, Comment, Setting | Standard Laravel resource controller with CRUD methods |
| **Form Request** | `app/Http/Requests/` | Dedicated validation classes for each endpoint |
| **API Resource** | `app/Http/Resources/` | JSON transformation layer between models and API responses |
| **Policy** | `app/Policies/` | Model-level authorization (Post, Comment, Page, User) |
| **Event + Listener** | `app/Events/`, `app/Listeners/` | Async side-effects via queued listeners (registration email, comment notification) |
| **Observer** | `app/Observers/SettingObserver` | Automatic cache invalidation on model changes |
| **Service** | `app/Services/` | Complex business logic encapsulation (Stripe subscriptions, billing) |
| **Job Batching** | `SettingUpdateAction` | `Bus::batch()` for bulk notification dispatch with then/catch/finally callbacks |
| **Enum (PHP 8.1)** | `app/Enums/UserRole` | Type-safe user role enumeration |
| **DB Transaction** | Controllers | Atomic write operations for data consistency |
| **Chunked Processing** | `NotifyAdminsAboutLoginJob` | `chunkById()` for memory-safe iteration over large datasets |

---

## CI/CD Pipeline

The project uses **GitLab CI/CD** with three stages.

### Pipeline Overview

```
┌─────────┐     ┌─────────┐     ┌─────────┐
│  test   │────→│  build  │────→│ deploy  │
│ (auto)  │     │(manual) │     │(manual) │
└─────────┘     └─────────┘     └─────────┘
```

### Stages

| Stage | Job | Trigger | Description |
|---|---|---|---|
| **test** | `app_test` | Automatic (every push) | Runs PHPUnit on SQLite in-memory (PHP 8.2 image) |
| **build** | `php_build` | Manual | Builds the PHP Docker image (deploy target) and pushes to Docker Hub |
| **build** | `nginx_build` | Manual | Builds the Nginx Docker image and pushes to Docker Hub |
| **deploy** | `deploy_aws` | Manual (only `main` branch) | Deploys to AWS ECS Fargate and runs migrations |

### Docker Registry

- **Repository:** `yurabh1990/admin-panel-repository`
- **PHP image tag:** `php.8.2`
- **Nginx image tag:** `nginx.latest`

### AWS Deployment

- **Cluster:** `admin-panel-yurabh`
- **Region:** `eu-north-1`
- **Launch type:** Fargate
- **Migration:** Runs as a one-off ECS task after deployment

---

## Docker Infrastructure

### Services

| Container | Image | Port | Purpose |
|---|---|---|---|
| `laravel-app` | Custom (multi-stage) | — | PHP-FPM application |
| `laravel-web` | `nginx:latest` | `8080:80` | Web server (proxies to PHP-FPM) |
| `laravel-db` | `mysql:8.0` | `3306:3306` | Database |
| `laravel-redis` | `redis:7` | `6379:6379` | Cache & queue backend |

### Dockerfile (Multi-stage)

The application uses a multi-stage Dockerfile located at `app/Docker/Dockerfile`:

| Stage | Purpose | Key Features |
|---|---|---|
| `base` | Common foundation | PHP extensions, Composer installation |
| `development` | Local development | Xdebug enabled, `pcntl` extension for Horizon |
| `deploy` | Production | `--no-dev`, `--optimize-autoloader`, no debug tools |

### Network

All containers communicate through a shared `laravel` bridge network.

### Xdebug

Xdebug is pre-configured in the development stage. Configuration file: `app/Docker/php/xdebug.ini`.

```bash
# Check Xdebug status
make xdebug-status

# IDE configuration
PHP_IDE_CONFIG="serverName=localhost"
```
