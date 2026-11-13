// @ts-check
const { defineConfig, devices } = require('@playwright/test');
const path = require('path');

const PHP_BIN = process.env.PHP_BIN || 'php';              // e.g. C:\xampp\php\php.exe
const PORT = process.env.E2E_PORT || '8080';
const EXTERNAL = process.env.E2E_BASE_URL;                 // set to test an already-running site (e.g. Apache)
const BASE_URL = (EXTERNAL || `http://localhost:${PORT}/`).replace(/\/?$/, '/');
const ROOT = path.resolve(__dirname, '../..');

module.exports = defineConfig({
  testDir: './specs',
  globalSetup: require.resolve('./global-setup.js'),
  timeout: 45_000,
  expect: { timeout: 10_000 },
  fullyParallel: false,          // tests share one database
  workers: 1,
  retries: process.env.CI ? 1 : 0,
  reporter: [['list'], ['html', { open: 'never' }]],
  use: {
    baseURL: BASE_URL,
    trace: 'retain-on-failure',
    screenshot: 'only-on-failure',
    video: 'off',
  },
  projects: [{ name: 'chromium', use: { ...devices['Desktop Chrome'] } }],

  // Start PHP's built-in server against the isolated e2e database (skipped when E2E_BASE_URL is set).
  webServer: EXTERNAL ? undefined : {
    command: `${PHP_BIN} -S localhost:${PORT} -t .`,
    cwd: ROOT,
    // a static file: always answers 200 once the server is listening (health.php can legitimately answer 503)
    url: `http://localhost:${PORT}/assets/css/modern-style.css`,
    reuseExistingServer: !process.env.CI,
    timeout: 30_000,
    env: {
      DB_NAME: process.env.E2E_DB_NAME || 'smartcanteen_e2e',
      APP_ENV: 'local',
      MAIL_ENABLED: 'false',
    },
  },
});
