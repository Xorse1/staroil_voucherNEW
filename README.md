# StarOil Voucher System

Professional fuel voucher purchasing portal for StarOil customers. The system supports voucher browsing, cart checkout, payment through external PSPs, wallet payment, purchased voucher management, profile/security settings, lubricant validation, and customer analytics.

## Current Stack

- PHP application served through Apache/XAMPP or a compatible Apache production host.
- Tailwind CSS loaded through CDN in the PHP pages.
- JavaScript frontend logic in `assets/app.js`.
- Composer dependency currently declared in `composer.json`.
- Environment configuration loaded from `.env` through `config.php`.
- Extensionless routing handled by `.htaccess`.

## Main User Flows

1. Guest lands on `store` and can browse voucher denominations.
2. User signs in or signs up before purchase.
3. Verified user adds vouchers to cart.
4. User selects payment method on `cart`.
5. `Pay Now` sends the user directly to the selected payment portal:
   - Hubtel: `checkout_process_hubtel.php`
   - Tingg: `checkout_process.php`
   - Wallet: `wallet_checkout_process.php`
6. Successful payment returns the user to the relevant success page.
7. Success pages clear both PHP cart session data and browser cart data.
8. User can view purchased vouchers from `vouchers`.

## Important Pages

- `index.php`: transition landing page for old and new systems.
- `store.php`: voucher store.
- `cart.php`: cart, payment gateway selection, and direct payment submit.
- `signin.php`, `signin_process.php`: login.
- `signup.php`, `register_process.php`: registration.
- `auth.php`: phone verification.
- `mfa.php`, `mfa_proccess.php`: MFA verification.
- `profile.php`: profile, security settings, avatar, OTP/authenticator setup links.
- `vouchers.php`: purchased voucher table/grid, filters, export.
- `voucher_update.php`, `voucher_update_process.php`: voucher activation/update.
- `vouchers_print_single.php`: single voucher print/download view.
- `wallet.php`: wallet dashboard and top-up.
- `analytics.php`: user purchase/activity analytics.
- `lube_authenticate.php`: lubricant validation page.
- `faqs.php`: FAQ page.

## Payment Files

- `checkout_process_hubtel.php`: creates voucher order, initiates Hubtel checkout, redirects to Hubtel checkout URL.
- `checkout_process.php`: creates voucher order, initiates Tingg checkout, redirects to Tingg checkout URL.
- `webhook_hubtel.php`: Hubtel voucher payment webhook.
- `webhook.php`: Tingg voucher payment webhook.
- `success_hubtel.php`: Hubtel success return page.
- `success.php`: Tingg success return page.
- `failed.php`: failed/cancelled payment page.

The checkout processors use `checkout_public_url()` from `includes/checkout_payload.php` for callback, return, and cancellation URLs. This depends on `APP_PUBLIC_URL` when configured.

## Wallet Files

- `wallet.php`: wallet UI.
- `wallet_balance.php`: frontend wallet balance fetch endpoint.
- `wallet_topup_process.php`: wallet top-up through Hubtel or Tingg.
- `wallet_checkout_process.php`: voucher order payment with wallet.
- `wallet_webhook_hubtel.php`: Hubtel wallet top-up webhook.
- `wallet_webhook_tingg.php`: Tingg wallet top-up webhook.
- `wallet_success.php`: wallet top-up success page.
- `success_wallet.php`: voucher purchase success page for wallet payment.
- `includes/wallet_api_client.php`: wallet API client and public URL helper.

Configured wallet API endpoints:

- `voucher_fetch_wallet_balance.php`
- `voucher_pay_order_with_wallet.php`
- `voucher_create_wallet_topup.php`
- `voucher_confirm_wallet_topup.php`

## Required Environment Variables

Create `.env` on the server. Do not commit real secrets.

```env
APP_PUBLIC_URL=https://app.staroil.services

API_KEY=
CLIENT_ID=
CLIENT_SECRET=

HUBTEL_API_USERNAME=
HUBTEL_API_PASSWORD=

ADD_BENEFICIARY_API_KEY=
ADD_VOUCHER_ORDER_API_KEY=
ARKESEL_API_KEY=

VOUCHER_WALLET_API_BASE_URL=https://fms.kayxappstaroil.com/APIs/voucher_api/voucher_wallet
VOUCHER_WALLET_BEARER_TOKEN=
```

`APP_PUBLIC_URL` must match the live domain exactly because PSP callback and return URLs are generated from it.

## Session Handling

Session configuration is centralized in `includes/session_config.php`.

- Session name: `STAROILVOUCHER`
- Cookie path: `/`
- `httponly`: enabled
- `samesite`: `Lax`
- `secure`: enabled automatically when HTTPS is detected

Use `includes/session_config.php` before `session_start()` on application entry pages.

## Cart Storage

There are two cart storage paths:

- PHP: `$_SESSION['shopping_cart']`
- Browser: `localStorage["staroil:cart"]`

The visible cart is primarily browser-side. Purchase success pages clear both PHP session cart data and browser `localStorage` cart keys.

## Security Notes

Production `.htaccess` blocks common sensitive files and folders:

- `.env`
- logs and text artifacts
- backup/database files
- `vendor/`
- `storage/`
- `config/`
- `includes/`
- hidden folders such as `.git`, `.codex`, `.agents`

Keep secrets in `.env`; do not hard-code live API keys into committed PHP files.

## Production Checklist

Before deploying:

1. Set `APP_PUBLIC_URL` to the production subdomain.
2. Confirm Hubtel and Tingg credentials are production credentials.
3. Confirm PSP callback URLs can reach:
   - `webhook_hubtel`
   - `webhook`
   - `wallet_webhook_hubtel`
   - `wallet_webhook_tingg`
4. Confirm `.htaccess` is active on the host.
5. Confirm Apache `AllowOverride` permits `.htaccess`.
6. Remove runtime logs from the web root or keep them blocked.
7. Run PHP syntax checks before upload.
8. Test sign in, sign up, OTP, MFA, cart, Hubtel payment, Tingg payment, wallet payment, voucher activation, voucher print, and logout.

## Useful Local Checks

```bash
php -l store.php
php -l cart.php
php -l checkout_process.php
php -l checkout_process_hubtel.php
php -l success.php
php -l success_hubtel.php
node --check assets/app.js
```

## Deployment Notes

- Upload application files to the production web root or subdomain root.
- Do not upload local `.env` values from development to production unless they are production-safe.
- Run `composer install --no-dev --optimize-autoloader` on production if dependencies are not uploaded.
- Ensure `vendor/` is present on the server even though it is ignored by Git.
- Ensure PHP cURL is enabled because PSP and API calls use cURL.
- Ensure HTTPS is active before taking payments.

## Known Operational Logs

The project may generate logs such as:

- `visits.log`
- `scan_log.txt`
- `callback_log.txt`
- `hubtel_webhook.log`
- `wallet_hubtel_webhook.log`
- `webhook.txt`

These are ignored by Git and blocked by `.htaccess`, but the safer production approach is to write logs outside the public web root.
