const fs = require('fs');
const path = require('path');
const crypto = require('crypto');
const { expect } = require('@playwright/test');

/** Reads KEY=VALUE pairs from the project's .env (only used for the Razorpay test secret). */
function readDotEnv() {
  const file = path.resolve(__dirname, '../../../.env');
  const out = {};
  if (!fs.existsSync(file)) return out;
  for (const line of fs.readFileSync(file, 'utf8').split(/\r?\n/)) {
    const m = line.match(/^\s*([A-Z0-9_]+)\s*=\s*(.*?)\s*$/);
    if (!m) continue;
    let v = m[2];
    if (/^["'].*["']$/.test(v)) v = v.slice(1, -1); else v = v.replace(/\s+#.*$/, '');
    out[m[1]] = v;
  }
  return out;
}
const dot = readDotEnv();

const ADMIN = {
  username: process.env.E2E_ADMIN_USER || 'e2e_admin',
  password: process.env.E2E_ADMIN_PASS || 'E2e-Admin-Pass1',
};
const RAZORPAY = {
  keyId: process.env.RAZORPAY_KEY_ID || dot.RAZORPAY_KEY_ID || '',
  secret: process.env.RAZORPAY_KEY_SECRET || dot.RAZORPAY_KEY_SECRET || '',
};
RAZORPAY.configured = RAZORPAY.keyId.startsWith('rzp_') && RAZORPAY.secret.length > 0;

const rand = () => Math.random().toString(36).slice(2, 8);

/** Weekday name in the app's timezone (the PHP app runs in Asia/Kolkata). */
function todayName() {
  return new Date().toLocaleDateString('en-US', { weekday: 'long', timeZone: 'Asia/Kolkata' });
}

const letters = (n) => Array.from({ length: n }, () => 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'[Math.floor(Math.random() * 26)]).join('');

function newStudent() {
  const id = rand();
  return {
    username: `e2e_${id}`,
    email: `e2e_${id}@example.com`,
    usn: `4GW23${letters(4)}${Math.floor(100 + Math.random() * 899)}`, // valid format, ~450M combinations
    dept: 'ISE',
    password: 'Passw0rd!',
  };
}

async function registerStudent(page, s) {
  await page.goto('student_register.php');
  await page.fill('input[name=username]', s.username);
  await page.fill('input[name=email]', s.email);
  await page.fill('input[name=usn]', s.usn);
  await page.fill('input[name=dept]', s.dept);
  await page.fill('input[name=password]', s.password);
  await page.click('.register-btn');
  await expect(page.locator('.swal2-popup')).toContainText('Registration Successful');
  await page.click('.swal2-confirm');
  await page.waitForURL('**/student_login.php');
}

async function loginStudent(page, s) {
  await page.goto('student_login.php');
  await page.fill('input[name=username]', s.username);
  await page.fill('input[name=password]', s.password);
  await page.click('.login-btn');
  await page.waitForURL('**/student_dashboard.php');
}

async function loginAdmin(page, creds = ADMIN) {
  await page.goto('admin_login.php');
  await page.fill('input[name=username]', creds.username);
  await page.fill('input[name=password]', creds.password);
  await page.click('.login-btn');
  await page.waitForURL('**/admin_dashboard.php');
}

/** Opens a weekday tab on the admin menu editor (the page always loads on Monday). */
async function openMenuDay(page, day) {
  await page.locator('.day-tab', { hasText: day }).click();
  return page.locator(`#day-${day}`);
}

/** Admin adds a dish for the given day; returns once the redirected menu page is back. */
async function addDish(adminPage, { name, price, day }) {
  await adminPage.goto('update_menu.php');
  const section = await openMenuDay(adminPage, day);
  const form = section.locator('.add-form');
  await form.locator('input[name=name]').fill(name);
  await form.locator('input[name=price]').fill(String(price));
  await form.locator('.btn-add').click();
  return adminPage;
}

async function addDishAndWait(adminPage, dish) {
  await addDish(adminPage, dish);
  await expect(adminPage.locator('.swal2-popup')).toContainText(/added successfully/i);
  await adminPage.waitForSelector('.day-tabs', { timeout: 15_000 });
}

/** What Razorpay's Checkout would send back after a successful payment. */
function signPayment(orderId, paymentId, secret = RAZORPAY.secret) {
  return crypto.createHmac('sha256', secret).update(`${orderId}|${paymentId}`).digest('hex');
}

module.exports = {
  ADMIN, RAZORPAY, rand, todayName, newStudent,
  registerStudent, loginStudent, loginAdmin, openMenuDay, addDish, addDishAndWait, signPayment,
};
