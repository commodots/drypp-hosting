# Drypp Integrated MVP

Laravel 12 application extending the existing Drypp email-marketing platform with the first hosting-commerce experience.

## What's included in this iteration

- Public Drypp homepage and service overview.
- Existing email-marketing authentication and campaign application retained.
- Authenticated customer hosting dashboard.
- Hosting package catalogue and order creation.
- Persistent guest and customer carts for domain registrations and hosting, with one combined order and Paystack checkout.
- Admin hosting control centre for servers, capacity, package resource limits, free-website eligibility and domain markup rules.
- Per-server and per-package capacity reservations while orders await payment.
- Paystack payment initialization and server-side verification callback.
- Hosting account records created as `pending_provisioning` after verified payment.
- Lead-generation landing page, ready for a permitted data-provider integration.

## Installation (Windows / XAMPP or Linux)

1. Extract this ZIP into a new project directory. Do not overwrite a live deployment before testing.
2. Copy `.env.example` to `.env`.
3. Configure `APP_URL`, database settings and mail settings in `.env`. Do not use production credentials from the previously uploaded archive.
4. Run `composer install`.
5. Run `php artisan key:generate`.
6. Create the MySQL database and configure `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD`.
7. Run `php artisan migrate --seed`.
8. Run `npm install` and `npm run build`.
9. Run `php artisan serve` and open the local URL shown by Artisan.

### Local administrator

The existing seed creates `admin@drypp.com` with password `password` for local development only. Change it immediately and do not use this credential on a public server.

## Provider integration status

- **Paystack:** hosted checkout initialization, server-side transaction verification, signed webhook verification, idempotent payment/service creation, and transaction records are implemented. Configure `PAYSTACK_SECRET_KEY` and test using Paystack test credentials before accepting real money. Set `PAYSTACK_CURRENCIES` explicitly to currencies confirmed on the merchant account; the sample `.env.example` enables NGN and USD, while the code fallback still includes NGN and USD if the variable is omitted. Confirm USD is enabled for the merchant account in Paystack. If callback or webhook verification is interrupted, safely reconcile with `php artisan paystack:reconcile <reference>`; do not ask customers to pay again.
- **Marketplace cart:** guest carts use random server-side session tokens and merge into the customer's cart at login or registration. Prices are calculated from the USD catalogue and rechecked with Porkbun before order creation. NGN checkout uses the configured USD-to-NGN display rate and is available only when NGN is enabled in `PAYSTACK_CURRENCIES`. Apply pending migrations with `php artisan migrate` before deploying this workflow.
- **Porkbun:** credentials are prepared in configuration, but live domain availability, registration, renewal and DNS API calls are not yet implemented. The domain field validates syntax only; it does not promise availability or register a domain.
- **Verpex WHM/cPanel:** server/package configuration and order records are implemented, but WHM account creation, usage sync, secure cPanel login and DNS automation are not yet connected. Paid accounts remain `pending_provisioning` until the provider connector is added.
- **Lead generation:** the customer-facing page is a placeholder. Automated sourcing and delivery require a selected authorized data provider, package definitions, privacy review and usage limits.
- **Free website offer:** eligibility is configurable per hosting package. The website delivery workflow/project tracker is a later phase.

Domain costs seeded in `DatabaseSeeder` are examples only. Replace them with verified costs before publishing prices. The current checkout charges for hosting only, not domain registration.

## Integration environment variables

```dotenv
PAYSTACK_PUBLIC_KEY=
PAYSTACK_SECRET_KEY=
PORKBUN_API_KEY=
PORKBUN_SECRET_KEY=
PORKBUN_SANDBOX=true
VERPEX_WHM_HOST=
VERPEX_WHM_USERNAME=
VERPEX_WHM_API_TOKEN=
```

Keep secrets only in the private `.env` file or deployment secret manager. The package intentionally excludes `.env`, `.git`, `vendor`, logs, local database files and the spreadsheet that was present in the uploaded archive.

## Validation performed

All PHP source, route, migration, seeder and test files passed `php -l` syntax checks in the build environment. Full Laravel route/test execution could not be completed in that environment because its PHP CLI lacks the DOM extension required by Laravel's console renderer. Run the test suite in your local PHP/XAMPP environment after installing the Composer dependencies.


### Hosting pricing note
The updated hosting catalogue seeds Standard Hosting ($30/year, 3 GB storage, 30 GB bandwidth, 10 email accounts, 2 databases), Business Hosting ($50/year, 6 GB storage, 60 GB bandwidth, 20 email accounts, 4 databases), and Professional Hosting ($100/year, 10 GB storage, 100 GB bandwidth, 40 email accounts, 10 databases). Currency selector values are illustrative display conversions, not live FX quotes. Verify and update exchange rates before launch. The existing payment flow must be updated and tested separately before accepting live USD charges.
