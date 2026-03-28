# Admin Panel API

REST API бекенд для адмін-панелі на Laravel 11. Надає JSON API для управління контентом сайту: постами, сторінками, категоріями, тегами, коментарями, налаштуваннями та користувачами.

## Технологічний стек

- **PHP** 8.2
- **Laravel** 11.x
- **MySQL** 8.0
- **Redis** — кеш та черги
- **Laravel Sanctum** — токен-автентифікація
- **Laravel Horizon** — моніторинг черг
- **AWS S3** — зберігання файлів
- **Docker** + **Nginx**
- **GitLab CI/CD** → **AWS ECS Fargate**
- **OpenAPI 3 / Swagger** — документація API

## Вимоги

- Docker & Docker Compose
- Make

## Запуск через Docker

```bash
# Клонувати репозиторій
git clone <repo-url>
cd admin-panel-main

# Скопіювати та заповнити змінні середовища
cp .env.example .env

# Зібрати та запустити контейнери
make build
make up

# Запустити міграції та сідери
make migrate
make seed
```

Додаток буде доступний на `http://localhost:8080`

### Змінні середовища (.env)

```env
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=laravel
DB_PASSWORD=secret

REDIS_HOST=redis
REDIS_PORT=6379

CACHE_STORE=redis
QUEUE_CONNECTION=redis

AWS_ACCESS_KEY_ID=your-key
AWS_SECRET_ACCESS_KEY=your-secret
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=your-bucket

MAIL_MAILER=smtp
MAIL_HOST=your-mail-host
MAIL_PORT=587
MAIL_USERNAME=your-username
MAIL_PASSWORD=your-password
MAIL_FROM_ADDRESS=noreply@example.com
```

## Makefile команди

```bash
make up            # Запустити контейнери
make down          # Зупинити контейнери
make restart       # Перезапустити контейнери
make build         # Зібрати образи без кешу

make shell         # Відкрити термінал всередині контейнера
make migrate       # Запустити міграції
make migrate-step  # Відкотити останню міграцію та перезапустити
make seed          # Заповнити БД тестовими даними
make fresh         # Скинути БД та запустити всі міграції з сідерами

make test          # Запустити тест-сюїт
make cache         # Очистити всі Laravel кеші
make tinker        # Відкрити Artisan Tinker
make xdebug-status # Перевірити статус Xdebug
```

## API Документація

Swagger UI доступний після запуску:

```
http://localhost:8080/api/documentation
```

Генерація/оновлення документації:

```bash
docker compose exec app php artisan l5-swagger:generate
```

## Структура API

### Публічні ендпоінти

| Метод | URL | Опис |
|-------|-----|------|
| POST | `/api/login` | Вхід (отримання токена) |
| POST | `/api/register` | Реєстрація нового користувача |
| POST | `/api/forgot/password` | Запит на скидання пароля |
| POST | `/api/password/reset` | Скидання пароля |

### Автентифіковані ендпоінти (Bearer token)

| Метод | URL | Опис |
|-------|-----|------|
| DELETE | `/api/logout` | Вихід |
| GET/POST/PUT/DELETE | `/api/users` | Управління користувачами |
| GET/POST/PUT/DELETE | `/api/comments` | Управління коментарями |

### Адмін ендпоінти (роль `admin`)

| Метод | URL | Опис |
|-------|-----|------|
| GET/POST/PUT/DELETE | `/api/admin/posts` | Управління постами |
| GET/POST/PUT/DELETE | `/api/admin/pages` | Управління сторінками |
| GET/POST/PUT/DELETE | `/api/admin/settings` | Системні налаштування |
| GET/POST/PUT/DELETE | `/api/admin/categories` | Категорії |
| GET/POST/PUT/DELETE | `/api/admin/tags` | Теги |

## Аутентифікація

API використовує Laravel Sanctum (Bearer token).

```bash
# Логін
POST /api/login
Content-Type: application/json

{
    "email": "admin@example.com",
    "password": "password"
}

# Відповідь
{
    "data": {
        "access_token": "1|abc123...",
        "token_type": "Bearer",
        "user": { "id": 1, "name": "Admin", "email": "...", "role": "admin" }
    }
}

# Використання токена
Authorization: Bearer 1|abc123...
```

## Ролі користувачів

| Роль | Доступ |
|------|--------|
| `admin` | Повний доступ до всіх ресурсів |
| `editor` | Доступ до своїх постів та коментарів |
| `user` | Читання та управління власними коментарями |

## Черги (Queues)

Проект використовує Redis-черги з моніторингом через Laravel Horizon.

```bash
# Запустити Horizon (всередині контейнера)
php artisan horizon
```

Horizon dashboard: `http://localhost:8080/horizon`

**Фонові задачі:**
- Сповіщення адміністраторів при кожному логіні
- Email підтвердження реєстрації
- Сповіщення автора посту про новий коментар
- Сповіщення користувача при зміні налаштувань

## Artisan команди

```bash
# Змінити роль користувача
php artisan app:change-user-role

# Опублікувати заплановані пости
php artisan app:publish-posts

# Видалити старі несхвалені коментарі (старші 30 днів)
php artisan app:clean-old-comments
```

## Тести

```bash
# Через Make
make test

# Або напряму
docker compose exec app php artisan test

# З детальним виводом
docker compose exec app php artisan test --verbose
```

Тести використовують SQLite in-memory БД. Конфігурація в `phpunit.xml`.

## Архітектура

Проект використовує **Action Pattern** — бізнес-логіка винесена з контролерів у окремі класи `app/Actions/`:

```
Controller → FormRequest (валідація) → Action (логіка) → Resource (відповідь)
```

**Ключові патерни:**
- **Action Pattern** — ізоляція бізнес-логіки
- **API Resources** — трансформація JSON відповідей
- **Form Requests** — валідація вхідних даних
- **Policies** — авторизація на рівні моделей
- **Events & Listeners** — подієво-орієнтована архітектура
- **Observer** — автоматична інвалідація кешу налаштувань
- **Jobs & Queues** — асинхронна обробка

## CI/CD

GitLab CI/CD pipeline (`/.gitlab-ci.yml`):

| Стейдж | Job | Тригер |
|--------|-----|--------|
| `test` | `app_test` — PHPUnit | Автоматично на кожен push |
| `build` | `php_build` — Docker build + push | Вручну |
| `build` | `nginx_build` — Nginx Docker build | Вручну |
| `deploy` | `deploy_aws` — AWS ECS deploy + migrate | Вручну (тільки `main`) |
