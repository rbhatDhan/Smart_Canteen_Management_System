const { test, expect } = require('@playwright/test');
const { todayName } = require('../helpers/app');

test.describe('public pages', () => {
  test('landing page loads and the Login button opens the role chooser', async ({ page }) => {
    await page.goto('index.php');
    await expect(page.locator('body')).toContainText(/canteen/i);

    const modal = page.locator('#roleModal');
    await expect(modal).not.toHaveClass(/active/);
    await page.locator('a.btn-login', { hasText: 'Login' }).click();
    await expect(modal).toHaveClass(/active/);
    await expect(modal.locator('a[href="student_login.php"]')).toBeVisible();
    await expect(modal.locator('a[href="admin_login.php"]')).toBeVisible();

    await modal.locator('.close-modal').click();
    await expect(modal).not.toHaveClass(/active/);
  });

  test('role chooser leads to the student login page', async ({ page }) => {
    await page.goto('index.php');
    await page.locator('a.btn-login', { hasText: 'Login' }).click();
    await page.locator('#roleModal a[href="student_login.php"]').click();
    await expect(page).toHaveURL(/student_login\.php/);
    await expect(page.locator('h2')).toContainText('Student Login');
  });

  test('weekly menu shows all seven days and switches tabs', async ({ page }) => {
    await page.goto('view_menu.php');
    await expect(page.locator('.day-tab')).toHaveCount(7);
    await expect(page.locator('#day-Monday')).toHaveClass(/active/);

    await page.locator('.day-tab', { hasText: 'Wednesday' }).click();
    await expect(page.locator('#day-Wednesday')).toHaveClass(/active/);
    await expect(page.locator('#day-Monday')).not.toHaveClass(/active/);
  });

  test('today is highlighted on the weekly menu', async ({ page }) => {
    await page.goto('view_menu.php');
    await expect(page.locator('.day-tab.today')).toHaveText(new RegExp(todayName()));
  });

  test('health endpoint answers with a status', async ({ request }) => {
    const res = await request.get('health.php');
    expect([200, 503]).toContain(res.status());
    const body = await res.json();
    expect(['ok', 'degraded']).toContain(body.status);
  });

  test('database tables are all present (health check)', async ({ request }) => {
    const body = await (await request.get('health.php')).json();
    // APP_ENV=local in the e2e server => detailed checks are included
    test.skip(!body.checks, 'detailed health checks are only shown in non-production mode');
    expect(body.checks.database.ok, body.checks.database.detail).toBe(true);
    expect(body.checks.tables.ok, body.checks.tables.detail).toBe(true);
    expect(body.checks.orders_columns.ok, body.checks.orders_columns.detail).toBe(true);
  });
});
