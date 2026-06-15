# Hyperlocal Jerry — Laravel API

The backend that powers the **customer mobile app**, the **restaurant web dashboard**,
the **rider app**, and the **admin web dashboard**. One Laravel API serves all four
clients; auth is token-based (Laravel Sanctum) and role-gated.

## Stack
- Laravel 12, PHP 8.2
- Sanctum (bearer tokens)
- SQLite by default (zero-config); swap `DB_*` in `.env` for MySQL/Postgres

## Run

```bash
cd backend
composer install
php artisan migrate:fresh --seed
# Bind to 0.0.0.0 so Expo Go on a physical phone can reach it:
php artisan serve --host=0.0.0.0 --port=8000
```

Then set the mobile app's `src/config.ts` → `API_BASE_URL` to
`http://<your-computer-LAN-IP>:8000/api` (e.g. `http://192.168.1.2:8000/api`).
The app falls back to bundled mock data when the API is unreachable.

## Demo accounts (password = `password`)
| Role | Email |
| --- | --- |
| Admin | `admin@hyperlocal.test` |
| Customer | `jerry@example.com` |
| Restaurant | `rest1@hyperlocal.test` … `rest8@hyperlocal.test` |
| Rider | `rider1@hyperlocal.test`, `rider2@hyperlocal.test` |

## Roles & order/delivery lifecycle

Order status: `placed → accepted → preparing → ready → rider_assigned → picked_up → on_the_way → delivered`
(`rejected` / `cancelled` as exits). The API maps these to the mobile app's
5-step timeline (`received / preparing / rider-assigned / on-the-way / delivered`).
Rider earns a flat **₦800** per delivered order (credited to their wallet).

## Endpoints

**Public**
```
POST /api/auth/register            POST /api/auth/login
GET  /api/categories               GET  /api/restaurants?search&category&sort_by&open_now&promotions&min_rating
GET  /api/restaurants/{id}         GET  /api/menu-items/{id}
GET  /api/restaurants/{id}/reviews GET  /api/coupons   POST /api/coupons/validate
```

**Authenticated (customer)**
```
GET  /api/auth/me    POST /api/auth/logout
GET  /api/orders     POST /api/orders     GET /api/orders/{id}   POST /api/orders/{id}/advance
POST /api/restaurants/{id}/reviews
GET/POST /api/addresses            GET/POST /api/complaints
```

**Restaurant dashboard** (`role:restaurant`, prefix `/api/restaurant`)
```
GET  orders   GET orders/history   GET sales
POST orders/{id}/accept | reject | preparing | ready
GET  menu   POST menu   PUT menu/{id}   DELETE menu/{id}   POST menu/{id}/availability
```

**Rider app** (`role:rider`, prefix `/api/rider`)
```
POST availability
GET  deliveries   GET deliveries/completed   GET earnings
POST deliveries/{id}/accept | decline | pickup | deliver
```

**Admin dashboard** (`role:admin`, prefix `/api/admin`)
```
POST restaurants (onboard)   POST riders (onboard)
GET  orders   GET riders/available   POST orders/{id}/assign
GET  complaints   POST complaints/{id}/resolve   GET revenue
```

## Web dashboards (restaurant + admin)
These are **web-based** and consume this same API. Build them as a separate SPA
(React/Vue) or with Laravel Blade/Inertia in this project — authenticate via
`POST /api/auth/login` and call the `role:`-gated endpoints above.
