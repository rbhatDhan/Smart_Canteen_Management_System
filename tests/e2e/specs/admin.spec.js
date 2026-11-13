const { test, expect } = require('@playwright/test');
const { loginAdmin, openMenuDay, addDishAndWait, addDish, todayName, rand } = require('../helpers/app');

test.describe('admin area', () => {
  test('dashboard, orders and statistics pages load', async ({ page }) => {
    await loginAdmin(page);
    await expect(page.locator('.stat-card').first()).toBeVisible();

    await page.goto('view_orders.php');
    await expect(page.locator('#ordersTable')).toBeVisible();

    await page.goto('admin_statistics.php');
    await expect(page.locator('canvas').first()).toBeVisible();
  });

  test('logout asks for confirmation and really ends the session', async ({ page }) => {
    await loginAdmin(page);
    await page.click('.logout-btn');
    await expect(page.locator('.swal2-popup')).toContainText('Are you sure?');
    await page.click('.swal2-confirm');
    await page.waitForURL('**/index.php');

    await page.goto('admin_dashboard.php');
    await expect(page).toHaveURL(/admin_login\.php/);
  });

  test('cancelling the logout keeps the admin logged in (regression)', async ({ page }) => {
    await loginAdmin(page);
    await page.click('.logout-btn');
    await page.click('.swal2-cancel');
    await page.waitForURL('**/admin_dashboard.php');

    await page.goto('view_orders.php'); // would bounce to the login page if the session had been destroyed
    await expect(page).toHaveURL(/view_orders\.php/);
  });
});

test.describe('menu management', () => {
  const day = todayName();
  const name = `E2E Dosa ${rand()}`;

  test.describe.configure({ mode: 'serial' });

  test('admin can add a dish', async ({ page }) => {
    await loginAdmin(page);
    await addDishAndWait(page, { name, price: 45, day });

    const section = await openMenuDay(page, day);
    await expect(section.locator('tr', { hasText: name })).toContainText('₹45.00');
  });

  test('adding the same dish twice on one day shows a duplicate warning', async ({ page }) => {
    await loginAdmin(page);
    await addDish(page, { name: name.toUpperCase(), price: 50, day });
    await expect(page.locator('.swal2-popup')).toContainText('Duplicate Dish');
  });

  test('students can see the new dish on the public menu', async ({ page }) => {
    await page.goto('view_menu.php');
    await page.locator('.day-tab', { hasText: day }).click();
    await expect(page.locator(`#day-${day}`)).toContainText(name);
  });

  test('admin can edit the price', async ({ page }) => {
    await loginAdmin(page);
    await page.goto('update_menu.php');
    let section = await openMenuDay(page, day);
    await section.locator('tr', { hasText: name }).locator('.btn-edit').click();

    section = await openMenuDay(page, day);
    const editRow = section.locator('tr').filter({ has: page.locator('input[name=name]') });
    await editRow.locator('input[name=price]').fill('60');
    await editRow.locator('.btn-save').click();

    await page.waitForSelector('.day-tabs');
    section = await openMenuDay(page, day);
    await expect(section.locator('tr', { hasText: name })).toContainText('₹60.00');
  });

  test('admin can delete a dish that has no orders', async ({ page }) => {
    await loginAdmin(page);
    await page.goto('update_menu.php');
    let section = await openMenuDay(page, day);
    await section.locator('tr', { hasText: name }).locator('.btn-delete').click();
    await page.click('.swal2-confirm');

    await page.waitForSelector('.day-tabs');
    section = await openMenuDay(page, day);
    await expect(section).not.toContainText(name);
  });

  test('a dish name with quotes cannot break the delete button', async ({ page }) => {
    const tricky = `Chef's "Special" ${rand()}`;
    await loginAdmin(page);
    await addDishAndWait(page, { name: tricky, price: 10, day });

    let section = await openMenuDay(page, day);
    await section.locator('tr', { hasText: 'Special' }).locator('.btn-delete').click();
    await expect(page.locator('.swal2-popup')).toContainText('Special');
    await page.click('.swal2-confirm');

    await page.waitForSelector('.day-tabs');
    section = await openMenuDay(page, day);
    await expect(section).not.toContainText(tricky);
  });
});
