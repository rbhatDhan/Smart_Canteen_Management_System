const { execFileSync } = require('child_process');
const path = require('path');

/** Wipes + seeds the e2e database before the run (not used when testing an external site). */
module.exports = async () => {
  if (process.env.E2E_BASE_URL) return;
  const php = process.env.PHP_BIN || 'php';
  const script = path.resolve(__dirname, 'setup_e2e_db.php');
  try {
    const out = execFileSync(php, [script], { encoding: 'utf8' });
    console.log(out.trim());
  } catch (err) {
    console.error('\nCould not prepare the e2e database. Is MySQL running and is PHP_BIN correct?\n');
    throw err;
  }
};
