const { test, expect } = require('@playwright/test');
const {
  RAZORPAY, rand, todayName, newStudent, registerStudent, loginStudent, loginAdmin, addDishAndWait, signPayment,
} = require('../helpers/app');

/**
 * Full customer journey: pick dishes -> pay (Razorpay test mode) -> order appears -> admin completes it -> history.
 * The Razorpay pop-up itself is third-party UI, so the payment is completed through the same HTTP calls the
 * pop-up makes (create order -> signed callback). Needs RAZORPAY_KEY_ID/SECRET (test keys) in .env.
 */
test.describe.configure({ mode: 'serial' });

const DISH = { name: `E2E Thali ${rand()}`, price: 25 };
const student = newStudent();

/** Card on the order page for our dish. */
const dishCard = (page) => page.locator('.dish-card', { hasText: DISH.name });

let dishId;

test.beforeAll(async ({ browser }) => {
  const ctx = await browser.newContext({ baseURL: test.info().project.use.baseURL });
  const admin = await ctx.newPage();
  await loginAdmin(admin);
  await addDishAndWait(admin, { ...DISH, day: todayName() });
  await ctx.close();

  const sctx = await browser.newContext({ baseURL: test.info().project.use.baseURL });
  const sp = await sctx.newPage();
  await registerStudent(sp, student);
  await sctx.close();
});

test.describe('order page', () => {
  test.beforeEach(async ({ page }) => {
    await loginStudent(page, student);
    await page.goto('place_order.php');
    await expect(dishCard(page)).toBeVisible();
  });

  test('quantity buttons update the total and Reset clears it', async ({ page }) => {
    const card = dishCard(page);
    const plus = card.locator('.qty-btn').last();
    const minus = card.locator('.qty-btn').first();

    await expect(page.locator('#totalPrice')).toHaveText('₹0');
    await plus.click();
    await plus.click();
    await expect(page.locator('#totalPrice')).toHaveText('₹50');
    await minus.click();
    await expect(page.locator('#totalPrice')).toHaveText('₹25');

    await page.click('#resetBtn');
    await expect(page.locator('#totalPrice')).toHaveText('₹0');
  });

  test('quantity is capped at 10 per dish', async ({ page }) => {
    const plus = dishCard(page).locator('.qty-btn').last();
    for (let i = 0; i < 12; i++) await plus.click();
    await expect(dishCard(page).locator('.qty-input')).toHaveValue('10');
    await expect(page.locator('#totalPrice')).toHaveText('₹250');
  });

  test('paying with an empty cart is refused with a hint', async ({ page }) => {
    await page.click('#payBtn');
    await expect(page.locator('.swal2-popup')).toContainText('No Items Selected');
  });

  test('Pay Now opens the Razorpay checkout', async ({ page }) => {
    test.skip(!RAZORPAY.configured, 'Razorpay test keys are not set in .env');
    await dishCard(page).locator('.qty-btn').last().click();
    await page.click('#payBtn');
    await expect(page.locator('iframe.razorpay-checkout-frame')).toBeVisible({ timeout: 30_000 });
  });
});

test.describe('payment through the API (what the checkout pop-up does)', () => {
  test.skip(!RAZORPAY.configured, 'Razorpay test keys are not set in .env');

  /** Reads the CSRF token and the dish id from the order page. */
  async function pageContext(page) {
    await page.goto('place_order.php');
    const csrf = await page.evaluate(() => window.CANTEEN_PAY.csrf);
    const idAttr = await dishCard(page).locator('.qty-input').getAttribute('id');
    dishId = idAttr.replace('qty-', '');
    return { csrf, headers: { 'X-CSRF-Token': csrf } };
  }

  test.beforeEach(async ({ page }) => {
    await loginStudent(page, student);
  });

  test('prices are decided by the server, not the browser', async ({ page }) => {
    const { headers } = await pageContext(page);
    const res = await page.request.post('api/payments_create.php', {
      headers,
      data: { items: { [dishId]: 2 }, amount: 1, price: 1 }, // extra "amount"/"price" fields must be ignored
    });
    expect(res.status()).toBe(200);
    const body = await res.json();
    expect(body.success).toBe(true);
    expect(body.amount).toBe(5000); // 2 x Rs 25 in paise
    expect(body.key).toMatch(/^rzp_/);
    expect(body.order_id).toMatch(/^order_/);
  });

  test('bad carts are rejected', async ({ page }) => {
    const { headers } = await pageContext(page);
    const post = (items) => page.request.post('api/payments_create.php', { headers, data: { items } });

    expect((await post({ 999999: 1 })).status()).toBe(422);   // dish not on today's menu
    expect((await post({ [dishId]: 11 })).status()).toBe(422); // more than 10
    expect((await post({})).status()).toBe(422);               // empty
  });

  test('requests without the CSRF token are refused', async ({ page }) => {
    await pageContext(page);
    const res = await page.request.post('api/payments_create.php', { data: { items: { [dishId]: 1 } } });
    expect(res.status()).toBe(419);
  });

  test('a forged signature creates no order', async ({ page }) => {
    const { headers } = await pageContext(page);
    const order = await (await page.request.post('api/payments_create.php', { headers, data: { items: { [dishId]: 1 } } })).json();

    const res = await page.request.post('api/payments_verify.php', {
      headers,
      data: { razorpay_order_id: order.order_id, razorpay_payment_id: `pay_${rand()}`, razorpay_signature: 'forged' },
    });
    expect(res.status()).toBe(400);
    expect((await res.json()).success).toBe(false);
  });

  test('a valid payment creates exactly one order, even if confirmed twice', async ({ page }) => {
    const { headers } = await pageContext(page);
    const order = await (await page.request.post('api/payments_create.php', { headers, data: { items: { [dishId]: 3 } } })).json();
    const paymentId = `pay_E2E${rand()}`;
    const confirm = { razorpay_order_id: order.order_id, razorpay_payment_id: paymentId, razorpay_signature: signPayment(order.order_id, paymentId) };

    const first = await page.request.post('api/payments_verify.php', { headers, data: confirm });
    expect(first.status()).toBe(200);
    const again = await page.request.post('api/payments_verify.php', { headers, data: confirm });
    expect(again.status()).toBe(200);

    await page.goto('student_dashboard.php');
    const rows = page.locator('.orders-table tbody tr', { hasText: DISH.name });
    await expect(rows).toHaveCount(1);
    await expect(rows.first()).toContainText('Pending');
    await expect(rows.first().locator('td').nth(2)).toHaveText('3');
  });

  test('the paid order appears in history as Pending and cannot be rated yet', async ({ page }) => {
    await page.goto('order_history.php');
    const row = page.locator('.orders-table tbody tr', { hasText: DISH.name });
    await expect(row).toHaveCount(1);
    await expect(row.locator('.status-badge')).toHaveText('Pending');
    await expect(row.locator('.rating-star')).toHaveCount(0);
    await expect(page.locator('.stat-card', { hasText: 'Member Since' })).toContainText(String(new Date().getFullYear()));

    // the API refuses a rating for an order that is not completed
    const csrf = await page.evaluate(() => window.CANTEEN_RATE.csrf);
    const key = Number((await row.locator('.order-id').innerText()).replace('#', ''));
    const early = await page.request.post('api/ratings_submit.php', { headers: { 'X-CSRF-Token': csrf }, data: { order: key, stars: 5 } });
    expect(early.status()).toBe(409);
    const bad = await page.request.post('api/ratings_submit.php', { headers: { 'X-CSRF-Token': csrf }, data: { order: key, stars: 9 } });
    expect(bad.status()).toBe(422);
    const missing = await page.request.post('api/ratings_submit.php', { headers: { 'X-CSRF-Token': csrf }, data: { order: 999999, stars: 3 } });
    expect(missing.status()).toBe(404);
  });

  test('the success banner shows after paying', async ({ page }) => {
    await page.goto('place_order.php?paid=1');
    await expect(page.locator('.alert-success')).toContainText('placed successfully');
  });

  test('another student cannot confirm someone else\'s payment', async ({ page, browser }) => {
    const { headers } = await pageContext(page);
    const order = await (await page.request.post('api/payments_create.php', { headers, data: { items: { [dishId]: 1 } } })).json();
    const paymentId = `pay_E2E${rand()}`;

    const otherCtx = await browser.newContext({ baseURL: test.info().project.use.baseURL });
    const other = await otherCtx.newPage();
    const mallory = newStudent();
    await registerStudent(other, mallory);
    await loginStudent(other, mallory);
    await other.goto('place_order.php');
    const csrf = await other.evaluate(() => window.CANTEEN_PAY.csrf);
    const res = await other.request.post('api/payments_verify.php', {
      headers: { 'X-CSRF-Token': csrf },
      data: { razorpay_order_id: order.order_id, razorpay_payment_id: paymentId, razorpay_signature: signPayment(order.order_id, paymentId) },
    });
    expect(res.status()).toBe(400);
    await otherCtx.close();
  });
});

test.describe('after payment', () => {
  test.skip(!RAZORPAY.configured, 'Razorpay test keys are not set in .env');

  test('admin marks the order Completed and the student sees it in history', async ({ browser }) => {
    const base = test.info().project.use.baseURL;

    // admin side
    const actx = await browser.newContext({ baseURL: base });
    const admin = await actx.newPage();
    await loginAdmin(admin);
    await admin.goto('view_orders.php');
    const row = admin.locator('#ordersTable tbody tr', { hasText: student.usn }).filter({ hasText: DISH.name }).first();
    await expect(row).toBeVisible();
    await expect(row.locator('.payment-badge')).toHaveText('Successful');

    await row.locator('.status-select').selectOption('Completed');
    await admin.click('.swal2-confirm');
    await expect(admin.locator('.swal2-popup')).toContainText('Updated!');
    await actx.close();

    // student side: order is now Completed and can be rated
    const sctx = await browser.newContext({ baseURL: base });
    const sp = await sctx.newPage();
    await loginStudent(sp, student);
    await sp.goto('order_history.php');
    const hrow = sp.locator('.orders-table tbody tr', { hasText: DISH.name });
    await expect(hrow.locator('.status-badge')).toHaveText('Completed');

    await hrow.locator('.rating-star').nth(3).click(); // 4th star
    await expect(sp.locator('.swal2-popup')).toContainText('Thanks for rating');

    await sp.reload();
    await expect(sp.locator('.orders-table tbody tr', { hasText: DISH.name }).locator('.rating-star.filled')).toHaveCount(4);

    // the dashboard "Rating" card now shows a real average instead of a fixed number
    await sp.goto('student_dashboard.php');
    await expect(sp.locator('.stat-card', { hasText: 'Rating' }).locator('.number')).toHaveText('4.0');
    await sctx.close();
  });
});
