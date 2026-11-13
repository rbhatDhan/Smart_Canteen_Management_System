# Testing guide

Two automated layers. Neither touches your real `smartcanteen` database.

| Layer | Tool | Database | What it proves |
|---|---|---|---|
| Unit + integration | PHPUnit | `smartcanteen_test` | payment signatures, server-side pricing, no duplicate orders, password upgrade, CSRF, reset tokens, repositories |
| End-to-end (browser) | Playwright | `smartcanteen_e2e` | real pages: register/login, access control, admin menu CRUD, order page, payment flow, admin completes order |

Prerequisite for both: XAMPP **MySQL running**, `.env` created (DB host/port/user/password).

## 1. PHPUnit

**Option A – single file, no Composer (easiest on XAMPP)**
```powershell
cd C:\xampp\htdocs\SmartCanteen
Invoke-WebRequest https://phar.phpunit.de/phpunit-10.phar -OutFile phpunit.phar
C:\xampp\php\php.exe phpunit.phar
```

**Option B – Composer**
```powershell
composer install
vendor\bin\phpunit
```

Useful: `--testsuite unit` (no database needed), `--filter PaymentFlowTest`, `--testdox` (readable list).
The `smartcanteen_test` database is created automatically from `database/schema.sql`. If MySQL isn't reachable the
integration tests are *skipped*, not failed. The suite refuses to run against any database whose name doesn't end in `_test`.

## 2. Playwright (browser tests)

Needs Node 18+.
```powershell
cd C:\xampp\htdocs\SmartCanteen\tests\e2e
npm install
npx playwright install chromium
$env:PHP_BIN = "C:\xampp\php\php.exe"     # PowerShell; use `set PHP_BIN=...` in cmd
npx playwright test
npx playwright show-report                # opens the HTML report (screenshots + traces for failures)
```
What happens: `global-setup.js` rebuilds the throw-away `smartcanteen_e2e` database (admin `e2e_admin`),
Playwright starts PHP's built-in server on port 8080 pointed at that database, and runs the specs in `specs/`.

Handy commands: `npm run test:headed` (watch the browser), `npm run test:ui` (interactive runner),
`npx playwright test specs/auth.spec.js`, `npx playwright test -g "logout"`.

**Payment specs** need your Razorpay **test** keys in `.env` and internet access (they create real test-mode
orders on Razorpay). Without keys they are skipped automatically. The Razorpay pop-up is third-party UI, so the
tests finish the payment through the same two HTTP calls the pop-up makes (create order, then a correctly
signed callback), and separately check that clicking **Pay Now** really opens the Razorpay frame.

**Testing your Apache site instead** (data goes into your real DB – use with care):
`$env:E2E_BASE_URL="http://localhost/SmartCanteen/"; $env:E2E_ADMIN_USER="admin1"; $env:E2E_ADMIN_PASS="..."`

## Extra suites added with the college-rules update
- Unit `RegistrationRulesTest`; integration `OrderGroupingTest` (one payment = one order, history shows pending orders,
  whole-order completion, legacy orders, per-student visibility, favourite dish) and `RatingAndThrottleTest`
  (rating average/replace, login lock after 5 failures, success clears it, per-IP lock).
- Playwright: USN/password/email rules, account lockout, forgot-password link, history shows Pending orders,
  ratings (API guards + clicking stars) and the dashboard Rating card showing the real average.

## What is covered
- Public: landing + role chooser, weekly menu tabs, `health.php`
- Auth: register, login by username or USN, logout/cancel, wrong password, duplicates, `.com` rule, short password,
  forgot-password does not reveal which emails exist
- Access control: anonymous / student / admin separation for pages and JSON APIs, unsigned webhook rejected
- Admin: dashboard/orders/stats load, logout confirm + cancel (regression), add/duplicate/edit/delete dish,
  quotes in dish names
- Orders: quantity buttons, 10-per-dish cap, empty-cart warning, Razorpay frame opens, server-side pricing, bad carts,
  CSRF, forged signature, exactly-once order creation, another student can't confirm your payment,
  admin completes order and it shows in the student's history

## Manual checks (can't be automated)
- Pay once with Razorpay's test card in the real pop-up (`4111 1111 1111 1111`, any future date/CVV).
- Receive the confirmation email once Gmail App Password is set in `.env`.
- Webhook: with a public URL (ngrok) configured in the Razorpay dashboard, close the tab right after paying and
  confirm the order still appears.
