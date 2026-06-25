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
- 📜 Auto-generated OpenAPI/Swagger documentation (`/v1/swagger.json`)
- 🧱 Built on Slim 4 (PSR-7/PSR-15) with dependency injection
- 👤 Per-customer, per-endpoint access management via the shop backend
- 📦 JSON request/response format throughout

## Technology Stack

- PHP
- Slim 4 (PSR-7 / PSR-15) + PHP-DI
- MySQL / MariaDB
- JWT (Firebase JWT / Tuupola middleware), HS256
- OpenAPI 3 (zircote/swagger-php)

## Authentication

Access is granted per customer account in the shop backend (**Customers → API Access**), then used in two steps:

1. **Get a token** - `POST /v1/oauth` with HTTP Basic-style credentials (`user`/`username` + `password` headers) for a customer with API access enabled. Returns a JWT valid for 10 minutes:
   ```json
   {
     "access_token": "<JWT>",
     "token_type": "Bearer",
     "expires": 1735000000
   }
   ```
2. **Call the API** - send the token on every subsequent request:
   ```
   Authorization: Bearer <JWT>
   ```

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

## Documentation

A live OpenAPI 3 specification is generated from the codebase and available at:

```
GET /v1/swagger.json
```

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
