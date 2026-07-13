# modified eCommerce REST API

A REST API for the modified eCommerce Shopsoftware - giving external applications and integrations programmatic access to shop data and operations.

## What is the modified API?

The **modified API** exposes the core entities of a modified shop (customers, products, orders, categories, …) as a versioned REST interface built on top of the existing shop core.

The project focuses on:

- Secure, token-based access to shop data
- Full CRUD coverage for the most relevant shop resources
- Per-customer access control, managed directly in the shop backend
- Self-documenting endpoints via an auto-generated OpenAPI specification

The API lets merchants and developers connect modified to external systems - ERPs, marketplaces, apps, custom storefronts - without touching the shop's admin UI.

## Key Features

- 🔐 JWT-based authentication with short-lived access tokens
- 🧩 Resource-oriented endpoints for customers, products, categories, orders, manufacturers, attributes, tags, coupons, campaigns, shipping, countries, contents, newsletters, currencies, languages, configurations and DHL
- 🔔 Signed webhooks that push shop events (e.g. new orders) to external systems, with automatic retries
- 📜 Auto-generated OpenAPI/Swagger documentation with an interactive Swagger UI (`/api/v1/docs/`)
- 🧱 Built on Slim 4 (PSR-7/PSR-15) with dependency injection
- 👤 Per-customer, per-endpoint access management via the shop backend
- 📦 JSON request/response format throughout

## Technology Stack

- PHP
- Slim 4 (PSR-7 / PSR-15) + PHP-DI
- MySQL / MariaDB
- JWT (Firebase JWT / Tuupola middleware), HS256
- OpenAPI 3 (zircote/swagger-php)

## Requirements

- modified eCommerce Shopsoftware **3.2.0** or higher
- PHP **8.4** or higher

The minimum shop version is enforced at runtime and reported by the [`/v1/version`](#version) endpoint. Both the API version and the minimum shop version are defined in one place in the API (`version` / `min_shop_version` in `config/settings.php`).

## Quick Start

Enable API access for a customer in the shop backend (**Customers → API Access**), then pick either way:

**In the browser (Swagger UI)**

1. Open `/api/v1/docs/` in your browser.
2. Click **Authorize**, enter the customer's username (email) and password.
3. Try any endpoint directly from the UI - the token is attached automatically.

**From the command line (cURL)**

```bash
# 1. Get a token (valid for 10 minutes)
curl -X POST https://your-shop.tld/api/v1/oauth \
  -H "username: api@example.com" \
  -H "password: your-password"
# → {"access_token":"<JWT>","token_type":"Bearer","expires":1735000000}

# 2. Call an endpoint with the token
curl https://your-shop.tld/api/v1/manufacturers \
  -H "Authorization: Bearer <JWT>"
```

> Replace `https://your-shop.tld` with your shop URL. If the shop runs in a subdirectory, prefix the paths accordingly (e.g. `https://your-shop.tld/shop/api/v1/...`).

## Authentication

Access is granted per customer account in the shop backend (**Customers → API Access**), then used in two steps:

1. **Get a token** - `POST /v1/oauth` with the credentials of a customer that has API access enabled. Credentials may be sent either as request headers (`user`/`username` + `password`) or as form fields (`username` + `password`, e.g. an OAuth2 *password* grant). Returns a short-lived JWT access token (10 minutes) plus a long-lived refresh token (30 days):
   ```json
   {
     "access_token": "<JWT>",
     "token_type": "Bearer",
     "expires": 1735000000,
     "refresh_token": "<opaque-token>",
     "refresh_expires": 1737592000
   }
   ```
2. **Call the API** - send the access token on every subsequent request:
   ```
   Authorization: Bearer <JWT>
   ```
3. **Refresh the token** - when the access token is about to expire, exchange the refresh token for a new pair via `POST /v1/oauth/refresh` (no credentials needed). The refresh token may be sent as a request header (`refresh_token`) or as a form field (`refresh_token`):
   ```bash
   curl -X POST https://your-shop.tld/api/v1/oauth/refresh \
     -H "refresh_token: <opaque-token>"
   ```
   The response has the same shape as `/v1/oauth`. Refresh tokens are **rotated**: each refresh invalidates the presented token and returns a new one, so always store the latest `refresh_token`. Tokens are stored only as a hash in the shop database and can be revoked; a refresh is rejected if the account no longer exists or its API access was removed.
4. **Log out** - revoke a refresh token via `POST /v1/oauth/logout` (the token itself is the proof of possession, so no credentials are needed). Add the `all` flag to revoke every refresh token of the account (log out on all devices):
   ```bash
   # revoke this session
   curl -X POST https://your-shop.tld/api/v1/oauth/logout \
     -H "refresh_token: <opaque-token>"

   # revoke all sessions of the account
   curl -X POST https://your-shop.tld/api/v1/oauth/logout \
     -H "refresh_token: <opaque-token>" -H "all: true"
   ```
   The call is idempotent and returns `{"success": true}` whenever a token is supplied. The access token is stateless and simply expires on its own after 10 minutes.

In the interactive docs you can skip the manual steps: click **Authorize**, enter the customer's username and password, and Swagger UI fetches the token and attaches it to every request automatically.

HTTPS is required in production; only `localhost`/`127.0.0.1` are allowed over plain HTTP.

## Endpoints Overview

All routes are served under `/api/v1/` (Slim group `/v1`). Resources cover full CRUD where applicable:

| Resource | Base path |
|---|---|
| Customers | `/customers` (incl. address book, basket, wishlist, memos, status history) |
| Categories | `/categories` |
| Products | `/products` (incl. images, attributes, tags, xsell, specials, reviews, content) |
| Manufacturers | `/manufacturers` |
| Attributes | `/attributes` (options & values) |
| Tags | `/tags` (options & values) |
| Orders | `/orders` (incl. products, totals, status history, tracking) |
| Countries | `/countries` (incl. geo zones, tax classes, tax rates) |
| Shipping | `/shipping` (carriers & status) |
| Contents | `/contents` |
| Campaigns | `/campaigns` |
| Currencies | `/currencies` |
| Languages | `/languages` |
| Newsletters | `/newsletters` |
| Configurations | `/configurations` |
| Coupons | `/coupons` |
| DHL | `/dhl` |
| Schema | `/schema/{table}` |
| Webhooks | `/webhooks` (subscriptions, event types, delivery log) |

## Webhooks

Instead of polling, external systems (ERPs, apps) can subscribe to shop events and get notified by a signed HTTP POST. Currently available event type: `order.created` (fired when an order is placed in the checkout).

### Setup (shop side)

1. Enable webhooks in the module settings (**Modules → System → API Access → Webhooks: true**). Events are only recorded while this is enabled.
2. **Set up a cron job** - this is required, without it nothing is ever delivered. The dispatcher endpoint is protected by a static secret, stored as `MODULE_API_ACCESS_WEBHOOKS_CRON_SECRET` in the shop's `configuration` table:
   ```
   */5 * * * * curl -fsS -H "X-Dispatch-Secret: <cron-secret>" https://your-shop.tld/api/v1/events/dispatch >/dev/null
   ```
   The secret may alternatively be passed as `?secret=` query parameter for cron panels that cannot set headers - prefer the header, query strings end up in server access logs. Hosts that can run PHP directly can use `includes/external/api/v1/bin/dispatch.php` instead. Concurrent runs are safe (a database lock makes extra calls no-ops).
3. Grant the `Webhook*` permissions to the API account (**Customers → API Access**). Subscribing to an event additionally requires the matching read permission (e.g. `order.created` requires *OrderGetOrders*) - `GET /v1/webhooks/event_types` shows all event types and whether the account may subscribe.

### Subscribing (consumer side)

```bash
curl -X POST https://your-shop.tld/api/v1/webhooks \
  -H "Authorization: Bearer <JWT>" -H "Content-Type: application/json" \
  -d '{"url": "https://erp.example.com/hooks/shop", "event_types": ["order.created"]}'
```

The URL must be `https` and publicly reachable. The response contains the signing `secret` **exactly once** - store it immediately, it cannot be retrieved again (create a new subscription if it is lost). Manage subscriptions via `GET/PUT/DELETE /v1/webhooks/{id}`; `GET /v1/webhooks/{id}/deliveries` shows the recent delivery attempts with status and errors for debugging.

### Receiving and verifying deliveries

Each delivery is a JSON POST with these headers:

```
X-Modified-Event: order.created
X-Modified-Delivery: <unique id, stable across retries - use it to deduplicate>
X-Modified-Timestamp: <unix time of this attempt>
X-Modified-Signature: sha256=<hex digest>
```

```json
{
  "id": 123,
  "event": "order.created",
  "created": 1766999940,
  "attempt": 1,
  "data": { "orders_id": 4711 },
  "links": { "order": "/api/v1/orders/4711" }
}
```

Payloads are deliberately thin (ids only) - fetch the full entity through the regular REST API using the `links`. Verify every delivery before processing it:

```php
$expected = 'sha256=' . hash_hmac(
    'sha256',
    $_SERVER['HTTP_X_MODIFIED_TIMESTAMP'] . '.' . file_get_contents('php://input'),
    $secret
);
$valid = hash_equals($expected, $_SERVER['HTTP_X_MODIFIED_SIGNATURE'] ?? '')
    && abs(time() - (int)$_SERVER['HTTP_X_MODIFIED_TIMESTAMP']) < 300;
```

Respond with any `2xx` status within 10 seconds to acknowledge.

### Retries and cleanup

Failed deliveries are retried up to 6 times with increasing backoff (2 min → 10 min → 1 h → 6 h → 24 h). After 10 consecutively failed deliveries a subscription is disabled automatically (`disabled_reason` says so); fix the receiver and re-enable it via `PUT /v1/webhooks/{id}` with `{"active": true}`. Processed events and delivery records are cleaned up automatically after ~30 days.

## Documentation

An interactive **Swagger UI** is available in the browser:

```
/api/v1/docs/
```

It is generated from the codebase and lets you browse every endpoint, inspect the schemas and call the API directly - including the built-in **Authorize** login (see *Authentication*). The docs resolve their paths relative to the current install, so they also work when the shop runs in a subdirectory.

The raw OpenAPI 3 specification is served at:

```
GET /v1/swagger.json
```

## Version

The currently running API version can be checked without authentication:

```
GET /v1/version
```
```json
{
  "version": "1.0.0",
  "requires": "3.2.0"
}
```

`requires` is the minimum shop version the API needs. The actually running shop version is not exposed.

## Contributing

Contributions are welcome!

If you want to help improve the modified API:

1. Fork the repository
2. Create a feature branch
3. Commit your changes
4. Open a pull request

Please keep changes clean, well documented and compatible with existing installations.

## Reporting Issues

Found a bug or have a feature request?

Please use the GitHub Issues section: [Open an Issue](../../issues)

## Community

- Official Website: https://www.modified-shop.org
- Community Forum: https://www.modified-shop.org/forum

## License

This project is open-source.
See the LICENSE file for details.

## Philosophy

The modified API is built for real integrations.

The goal is not to chase short-lived API trends, but to provide a stable, well-documented and secure interface that developers and merchants can rely on long-term.
