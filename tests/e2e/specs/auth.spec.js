const { test, expect } = require('@playwright/test');
const { newStudent, registerStudent, loginStudent, loginAdmin, ADMIN } = require('../helpers/app');

test.describe('student accounts', () => {
  test('register, log in, see the dashboard, log out', async ({ page }) => {
    const s = newStudent();
    await registerStudent(page, s);
    await loginStudent(page, s);

    await expect(page.locator('h1')).toContainText(`Welcome back, ${s.username}`);

    await page.click('.logout-btn');
    await expect(page.locator('h2')).toContainText('Ready to Leave?');
    await page.click('button[name=confirm_logout]');
    await expect(page).toHaveURL(/index\.php|\/$/);

    // session is really gone
    await page.goto('student_dashboard.php');
    await expect(page).toHaveURL(/student_login\.php/);
  });

  test('cancelling logout keeps the student logged in', async ({ page }) => {
    const s = newStudent();
    await registerStudent(page, s);
    await loginStudent(page, s);

    await page.click('.logout-btn');
    await page.click('button[name=cancel_logout]');
    await expect(page).toHaveURL(/student_dashboard\.php/);
  });

  test('can log in with the USN instead of the username', async ({ page }) => {
    const s = newStudent();
    await registerStudent(page, s);
    await page.goto('student_login.php');
    await page.fill('input[name=username]', s.usn);
    await page.fill('input[name=password]', s.password);
    await page.click('.login-btn');
    await expect(page).toHaveURL(/student_dashboard\.php/);
  });

  test('wrong password is rejected with a clear message', async ({ page }) => {
    const s = newStudent();
    await registerStudent(page, s);
    await page.goto('student_login.php');
    await page.fill('input[name=username]', s.username);
    await page.fill('input[name=password]', 'definitely-wrong');
    await page.click('.login-btn');
    await expect(page.locator('.error-message')).toContainText('Invalid username/USN or password');
    await expect(page).toHaveURL(/student_login\.php/);
  });

  test('registering the same USN or email twice is blocked', async ({ page }) => {
    const s = newStudent();
    await registerStudent(page, s);

    await page.goto('student_register.php');
    await page.fill('input[name=username]', s.username + 'x');
    await page.fill('input[name=email]', s.email);
    await page.fill('input[name=usn]', s.usn);
    await page.fill('input[name=dept]', s.dept);
    await page.fill('input[name=password]', s.password);
    await page.click('.register-btn');
    await expect(page.locator('.error-message')).toContainText(/already exists/i);
  });

  test('any valid email domain is accepted (not just .com)', async ({ page }) => {
    const s = newStudent();
    s.email = `${s.username}@college.edu.in`;
    await registerStudent(page, s);
    await loginStudent(page, s);
  });

  test('malformed USNs are rejected', async ({ page }) => {
    const s = newStudent();
    await page.goto('student_register.php');
    await page.fill('input[name=username]', s.username);
    await page.fill('input[name=email]', s.email);
    await page.fill('input[name=usn]', 'ABC');
    await page.fill('input[name=dept]', s.dept);
    await page.fill('input[name=password]', s.password);
    await page.click('.register-btn');
    await expect(page.locator('.error-message')).toContainText(/valid USN/i);
  });

  test('a USN typed without the college prefix is completed automatically', async ({ page }) => {
    const s = newStudent();
    const short = s.usn.replace('4GW', '');
    await page.goto('student_register.php');
    await page.fill('input[name=username]', s.username);
    await page.fill('input[name=email]', s.email);
    await page.fill('input[name=usn]', short);
    await page.fill('input[name=dept]', s.dept);
    await page.fill('input[name=password]', s.password);
    await page.click('.register-btn');
    await expect(page.locator('.swal2-popup')).toContainText('Registration Successful');

    // the stored USN has the prefix, so logging in with the full USN works
    await page.click('.swal2-confirm');
    await page.fill('input[name=username]', s.usn);
    await page.fill('input[name=password]', s.password);
    await page.click('.login-btn');
    await expect(page).toHaveURL(/student_dashboard\.php/);
  });

  test('weak passwords are rejected', async ({ page }) => {
    const s = newStudent();
    for (const weak of ['123', 'abcdefgh', '12345678']) {
      await page.goto('student_register.php');
      await page.fill('input[name=username]', s.username);
      await page.fill('input[name=email]', s.email);
      await page.fill('input[name=usn]', s.usn);
      await page.fill('input[name=dept]', s.dept);
      await page.fill('input[name=password]', weak);
      await page.evaluate(() => document.querySelector('input[name=password]').removeAttribute('minlength'));
      await page.click('.register-btn');
      await expect(page.locator('.error-message'), weak).toContainText(/8-72 characters/);
    }
  });

  test('five wrong passwords lock the account for a while', async ({ page }) => {
    const s = newStudent();
    await registerStudent(page, s);

    for (let i = 0; i < 5; i++) {
      await page.goto('student_login.php');
      await page.fill('input[name=username]', s.username);
      await page.fill('input[name=password]', `wrong-${i}`);
      await page.click('.login-btn');
      await expect(page.locator('.error-message')).toContainText('Invalid username/USN or password');
    }

    // even the right password is refused while locked
    await page.goto('student_login.php');
    await page.fill('input[name=username]', s.username);
    await page.fill('input[name=password]', s.password);
    await page.click('.login-btn');
    await expect(page.locator('.error-message')).toContainText('Too many failed attempts');
    await expect(page).toHaveURL(/student_login\.php/);
  });

  test('the login page links to password reset', async ({ page }) => {
    await page.goto('student_login.php');
    await page.click('text=Forgot password?');
    await expect(page).toHaveURL(/forgot_password\.php/);
  });

  test('forgot-password gives the same answer for known and unknown emails', async ({ page }) => {
    const s = newStudent();
    await registerStudent(page, s);

    for (const email of [s.email, 'nobody-here@example.com']) {
      await page.goto('forgotPassword/forgot_password.php');
      await page.fill('input[name=email]', email);
      await page.click('input[type=submit]');
      await expect(page.locator('body')).toContainText('If that email is registered');
    }
  });
});

test.describe('access control', () => {
  test('anonymous visitors are sent to the login pages', async ({ page }) => {
    await page.goto('student_dashboard.php');
    await expect(page).toHaveURL(/student_login\.php/);

    await page.goto('place_order.php');
    await expect(page).toHaveURL(/student_login\.php/);

    await page.goto('order_history.php');
    await expect(page).toHaveURL(/student_login\.php/);

    for (const url of ['admin_dashboard.php', 'view_orders.php', 'update_menu.php', 'admin_statistics.php']) {
      await page.goto(url);
      await expect(page, url).toHaveURL(/admin_login\.php/);
    }
  });

  test('a logged-in student cannot open admin pages', async ({ page }) => {
    const s = newStudent();
    await registerStudent(page, s);
    await loginStudent(page, s);
    for (const url of ['admin_dashboard.php', 'view_orders.php', 'update_menu.php']) {
      await page.goto(url);
      await expect(page, url).toHaveURL(/admin_login\.php/);
    }
  });

  test('the order-status API refuses anonymous and student callers', async ({ request, page }) => {
    const anon = await request.post('api/orders_update_status.php', { form: { id: '1', status: 'Completed' } });
    expect(anon.status()).toBe(401);

    const s = newStudent();
    await registerStudent(page, s);
    await loginStudent(page, s);
    const asStudent = await page.request.post('api/orders_update_status.php', { form: { id: '1', status: 'Completed' } });
    expect(asStudent.status()).toBe(403);
  });

  test('payment APIs refuse anonymous callers', async ({ request }) => {
    for (const url of ['api/payments_create.php', 'api/payments_verify.php', 'api/payments_failed.php', 'api/ratings_submit.php']) {
      const res = await request.post(url, { data: {} });
      expect(res.status(), url).toBe(401);
    }
  });

  test('the Razorpay webhook rejects unsigned calls', async ({ request }) => {
    const res = await request.post('api/razorpay_webhook.php', { data: { event: 'order.paid' } });
    expect(res.status()).toBe(400);
  });
});

test.describe('admin login', () => {
  test('valid admin credentials open the dashboard', async ({ page }) => {
    await loginAdmin(page, ADMIN);
    await expect(page).toHaveURL(/admin_dashboard\.php/);
  });

  test('wrong credentials show one generic error', async ({ page }) => {
    for (const creds of [
      { username: ADMIN.username, password: 'wrong-password' },
      { username: 'no_such_admin', password: 'whatever' },
    ]) {
      await page.goto('admin_login.php');
      await page.fill('input[name=username]', creds.username);
      await page.fill('input[name=password]', creds.password);
      await page.click('.login-btn');
      await expect(page.locator('.error-message')).toContainText('Invalid username or password');
    }
  });

  test('a student account cannot log in through the admin form', async ({ page }) => {
    const s = newStudent();
    await registerStudent(page, s);
    await page.goto('admin_login.php');
    await page.fill('input[name=username]', s.username);
    await page.fill('input[name=password]', s.password);
    await page.click('.login-btn');
    await expect(page.locator('.error-message')).toBeVisible();
    await expect(page).toHaveURL(/admin_login\.php/);
  });
});
