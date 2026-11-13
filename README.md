# 🍽 Smart Canteen Management System (modular edition)

Same screens, same flows as before — reorganised into modules, hardened, and with **Razorpay (test mode)** checkout.

## Setup (XAMPP)

1. Copy this folder to `htdocs/SmartCanteen` and start **Apache + MySQL**.
2. **Database**
   - Fresh install: import `database/schema.sql` in phpMyAdmin.
   - Existing database: back it up, then run `database/migrations/001_upgrade_existing_db.sql` and then
     `database/migrations/002_ratings_throttle_member_since.sql` (real ratings, "Member Since", login attempt limits).
3. Copy `.env.example` → `.env` and fill in DB, Razorpay and mail settings.
4. Open `http://localhost/SmartCanteen/`.

### Create your first admin (fresh install only)
Passwords are stored hashed. In a terminal run
`php -r "echo password_hash('YourStrongPassword', PASSWORD_DEFAULT);"` and then in phpMyAdmin:

```sql
INSERT INTO users (username, password, role) VALUES ('admin1', '<paste the hash>', 'admin');
```
(Existing databases: old plain-text passwords keep working and are converted to hashes on each user's next login.)

## Razorpay test mode

1. Razorpay Dashboard → switch to **Test Mode** → *Account & Settings → API Keys* → generate keys.
2. Put them in `.env` (`RAZORPAY_KEY_ID=rzp_test_…`, `RAZORPAY_KEY_SECRET=…`).
3. Log in as a student → *Place Order* → pick dishes → **Pay Now**. Use Razorpay's test credentials
   (e.g. test card `4111 1111 1111 1111`, any future expiry/CVV; test UPI `success@razorpay` / `failure@razorpay` —
   see Razorpay's "Test Card Details" docs for the current list).
4. Going live later = replace the test keys with live keys. No code change.

**How it works** (`src/Modules/Payments`)
- `api/payments_create.php` prices the cart **from the database** (never trusts browser prices), then creates a Razorpay order.
- After payment, `api/payments_verify.php` checks Razorpay's HMAC signature and only then creates the order rows.
- Optional but recommended: add a webhook in the Razorpay dashboard → `https://<your-public-url>/api/razorpay_webhook.php`
  (events `order.paid`, `payment.captured`, `payment.failed`) with the secret from `RAZORPAY_WEBHOOK_SECRET`.
  It completes orders if a student closes the tab right after paying. (Localhost needs a tunnel such as ngrok.)
- Finalising is idempotent: browser callback and webhook can both arrive and the order is still created once.

## How the app behaves (college canteen rules)
- **One checkout = one order.** Several dishes paid together show as a single order (number = payment id) with its items listed;
  the admin completes the whole order at once and the student is emailed. Orders from before payments existed show as `#L<id>`.
- **Order history lists every order** (Pending and Completed). Completed orders can be rated 1-5 stars; the dashboard "Rating"
  is the real average of all ratings ("–" until someone rates). "Member Since" is the account's real sign-up year.
- **Registration:** USN must look like `4GW23IS056` (prefix is added automatically; set `USN_PREFIX`), any valid email,
  password 8+ characters with a letter and a number, unique USN / email / name.
- **Login:** 5 wrong passwords lock that account for 15 minutes (and 20 failures lock an IP); sessions expire after
  `SESSION_IDLE_MINUTES`; "Forgot password?" is on the login page.
- **Opening hours:** set `CANTEEN_OPEN` / `CANTEEN_CLOSE` in `.env` to refuse orders outside them.

## Health check
`/health.php` returns `{"status":"ok"}` (or `degraded`). When `APP_ENV=local`, or when you're logged in as admin,
it lists every check (PHP extensions, database, tables, Razorpay keys, mail, log folder) with a fix hint for each failure.

## Tests
See [TESTING.md](TESTING.md): PHPUnit (`phpunit.phar`) and Playwright (`tests/e2e`).

## Structure

```
index.php, student_*.php, admin_*.php, …   thin entry points (same URLs as before)
api/                 JSON endpoints (payments, order status, webhook)
src/Core/            Env, Database, Session, Auth, Csrf, Http, View, Passwords, Logger
src/Modules/
  Auth/  Menu/  Orders/  Payments/  Profile/  Dashboard/  Notifications/
  (each: Controller + Repository / Service)
src/Views/           the original pages' HTML, unchanged visually
assets/css, js, img  stylesheets (per-page CSS split out verbatim), checkout JS, images
database/            schema + migration
lib/PHPMailer        mail library
tests/               PHPUnit tests; tests/e2e = Playwright browser tests
health.php           readiness / uptime endpoint
legacy/              unused files from the old project (blocked from the web)
storage/logs         app.log (errors, payment/webhook problems)
```

