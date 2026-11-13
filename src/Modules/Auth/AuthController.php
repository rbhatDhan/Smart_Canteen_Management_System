<?php
declare(strict_types=1);

namespace SmartCanteen\Modules\Auth;

use SmartCanteen\Core\Auth;
use SmartCanteen\Core\Csrf;
use SmartCanteen\Core\Env;
use SmartCanteen\Core\Http;
use SmartCanteen\Core\Passwords;
use SmartCanteen\Core\Session;
use SmartCanteen\Core\View;
use SmartCanteen\Modules\Notifications\Mailer;

final class AuthController
{
    private const LOCKED_MESSAGE = 'Too many failed attempts. Please try again in 15 minutes.';

    private UserRepository $users;

    public function __construct()
    {
        $this->users = new UserRepository();
    }

    // ---- Student login -------------------------------------------------

    public function studentLoginForm(): void
    {
        View::render('auth/student_login', ['error' => $_GET['error'] ?? null, 'msg' => $_GET['msg'] ?? null]);
    }

    public function studentLogin(): void
    {
        Csrf::require();
        $input = trim((string) ($_POST['username'] ?? ''));
        $pass  = trim((string) ($_POST['password'] ?? ''));
        if ($input === '' || $pass === '') {
            Http::redirect('student_login.php?error=' . rawurlencode('Please fill all fields'));
        }

        $throttle = new LoginThrottle();
        $ip = LoginThrottle::clientIp();
        if ($throttle->isLocked('student', $input, $ip)) {
            Http::redirect('student_login.php?error=' . rawurlencode(self::LOCKED_MESSAGE));
        }

        $user = $this->users->findStudentByLogin($input);
        if ($user && Passwords::verifyAndUpgrade($user, $pass, [$this->users, 'updatePassword'])) {
            $throttle->recordSuccess('student', $input);
            Session::login($user, 'student');
            Http::redirect('student_dashboard.php');
        }
        $throttle->recordFailure('student', $input, $ip);
        Http::redirect('student_login.php?error=' . rawurlencode('Invalid username/USN or password'));
    }

    // ---- Admin login ---------------------------------------------------

    public function adminLoginForm(): void
    {
        View::render('auth/admin_login', ['error' => $_GET['error'] ?? null]);
    }

    public function adminLogin(): void
    {
        Csrf::require();
        $username = trim((string) ($_POST['username'] ?? ''));
        $password = trim((string) ($_POST['password'] ?? ''));

        $throttle = new LoginThrottle();
        $ip = LoginThrottle::clientIp();
        if ($username !== '' && $throttle->isLocked('admin', $username, $ip)) {
            Http::redirect('admin_login.php?error=' . rawurlencode(self::LOCKED_MESSAGE));
        }

        $user = $username !== '' ? $this->users->findAdminByUsername($username) : null;
        if ($user && Passwords::verifyAndUpgrade($user, $password, [$this->users, 'updatePassword'])) {
            $throttle->recordSuccess('admin', $username);
            Session::login($user, 'admin');
            Http::redirect('admin_dashboard.php');
        }
        if ($username !== '') {
            $throttle->recordFailure('admin', $username, $ip);
        }
        // One generic message so usernames can't be probed.
        Http::redirect('admin_login.php?error=' . rawurlencode('Invalid username or password'));
    }

    // ---- Registration --------------------------------------------------

    public function registerForm(): void
    {
        View::render('auth/student_register', ['error' => null, 'registered' => false]);
    }

    public function register(): void
    {
        Csrf::require();
        $username = trim((string) ($_POST['username'] ?? ''));
        $email    = trim((string) ($_POST['email'] ?? ''));
        $password = trim((string) ($_POST['password'] ?? ''));
        $usn      = trim((string) ($_POST['usn'] ?? ''));
        $dept     = trim((string) ($_POST['dept'] ?? ''));
        $error    = null;
        $usn      = RegistrationRules::normalizeUsn($usn);

        if ($username === '' || $password === '' || $email === '' || $usn === '' || $dept === '') {
            $error = 'All fields are required.';
        } else {
            $error = RegistrationRules::usernameError($username)
                ?? RegistrationRules::emailError($email)
                ?? RegistrationRules::usnError($usn)
                ?? RegistrationRules::departmentError($dept)
                ?? RegistrationRules::passwordError($password);
        }

        if ($error === null) {
            if ($this->users->usnOrEmailExists($usn, $email)) {
                $error = 'USN or Email already exists.';
            } else {
                try {
                    $this->users->createStudent($username, $email, Passwords::hash($password), $usn, $dept);
                    View::render('auth/student_register', ['error' => null, 'registered' => true]);
                    return;
                } catch (\mysqli_sql_exception $ex) {
                    $error = $ex->getCode() === 1062
                        ? 'Username, USN or Email already exists.'
                        : 'Registration failed. Please try again.';
                }
            }
        }
        View::render('auth/student_register', ['error' => $error, 'registered' => false]);
    }

    // ---- Logout --------------------------------------------------------

    public function studentLogout(): void
    {
        if (!Auth::loggedIn()) {
            Http::redirect('index.php');
        }
        if (Http::isPost()) {
            Csrf::require();
            if (isset($_POST['confirm_logout'])) {
                Session::destroy();
                Http::redirect('index.php');
            }
            Http::redirect(Auth::dashboardUrl());
        }
        View::render('auth/student_logout');
    }

    public function adminLogout(): void
    {
        if (Http::isPost()) {
            Csrf::require();
            Session::destroy();
            Http::json(['success' => true]);
        }
        View::render('auth/admin_logout');
    }

    // ---- Forgot / reset password --------------------------------------

    public function forgotForm(): void
    {
        View::render('auth/forgot_password', ['msg' => $_GET['msg'] ?? null, 'error' => $_GET['error'] ?? null]);
    }

    public function forgotSubmit(): void
    {
        Csrf::require();
        $email = trim((string) ($_POST['email'] ?? ''));
        $user = $email !== '' ? $this->users->findByEmail($email) : null;

        if ($user) {
            $token = bin2hex(random_bytes(32));
            $this->users->saveResetToken((int) $user['id'], $token, date('Y-m-d H:i:s', strtotime('+1 hour')));
            $base = rtrim((string) Env::get('APP_URL', 'http://localhost/SmartCanteen'), '/');
            $link = $base . '/forgotPassword/reset_password.php?token=' . $token;
            $body = '<p>Click this link to reset your password (valid for 1 hour):</p>'
                  . '<p><a href="' . e($link) . '">' . e($link) . '</a></p>'
                  . '<p>If you did not request this, you can ignore this email.</p>';
            (new Mailer())->send((string) $user['email'], (string) $user['username'], 'Password Reset Link', $body);
        }
        // Same response whether or not the email exists (prevents account enumeration).
        Http::redirect('forgot_password.php?msg=' . rawurlencode('If that email is registered, a reset link has been sent.'));
    }

    public function resetForm(): void
    {
        $token = trim((string) ($_GET['token'] ?? ''));
        if ($token === '' || !$this->users->findByValidResetToken($token)) {
            exit('Invalid or expired token');
        }
        View::render('auth/reset_password', ['error' => '']);
    }

    public function resetSubmit(): void
    {
        Csrf::require();
        $token = trim((string) ($_GET['token'] ?? ''));
        $user = $token !== '' ? $this->users->findByValidResetToken($token) : null;
        if (!$user) {
            exit('Invalid or expired token');
        }
        $new = trim((string) ($_POST['new_password'] ?? ''));
        $confirm = trim((string) ($_POST['confirm_password'] ?? ''));
        $error = '';
        if ($new !== $confirm) {
            $error = 'Passwords do not match';
        } elseif (RegistrationRules::passwordError($new) !== null) {
            $error = RegistrationRules::passwordError($new);
        } else {
            $this->users->resetPassword((int) $user['id'], Passwords::hash($new));
            Http::redirect('../student_login.php?msg=' . rawurlencode('Password reset successfully!'));
        }
        View::render('auth/reset_password', ['error' => $error]);
    }
}
