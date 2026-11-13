<?php
declare(strict_types=1);

namespace SmartCanteen\Modules\Profile;

use SmartCanteen\Core\Auth;
use SmartCanteen\Core\Csrf;
use SmartCanteen\Core\Http;
use SmartCanteen\Core\Passwords;
use SmartCanteen\Core\View;
use SmartCanteen\Modules\Auth\RegistrationRules;
use SmartCanteen\Modules\Auth\UserRepository;

final class ProfileController
{
    private UserRepository $users;

    public function __construct()
    {
        $this->users = new UserRepository();
    }

    public function show(): void
    {
        if (!Auth::loggedIn()) {
            exit('<h3>Please login first to view your profile.</h3>');
        }
        $row = $this->users->findById(Auth::id());
        if (!$row) {
            exit('<h3>User not found!</h3>');
        }
        View::render('profile/view_profile', ['row' => $row]);
    }

    public function edit(): void
    {
        if (!Auth::loggedIn()) {
            echo "<script>alert('Please login first!'); window.location='student_login.php';</script>";
            exit;
        }
        $user = $this->users->findById(Auth::id());
        if (!$user) {
            echo "<script>alert('User not found!'); window.location='view_profile.php';</script>";
            exit;
        }

        $error = null;
        $updated = false;

        if (Http::isPost() && isset($_POST['update'])) {
            Csrf::require();
            $username = trim((string) ($_POST['username'] ?? ''));
            $email    = trim((string) ($_POST['email'] ?? ''));
            $dept     = trim((string) ($_POST['dept'] ?? ''));
            $usn      = trim((string) ($_POST['usn'] ?? ''));
            $newPass  = (string) ($_POST['password'] ?? '');

            $isAdmin = ($user['role'] ?? '') === 'admin';
            if ($isAdmin) { // admins have no USN / department: keep whatever is stored
                $usn  = (string) ($user['usn'] ?? '');
                $dept = (string) ($user['dept'] ?? '');
            } else {
                $usn = RegistrationRules::normalizeUsn($usn);
            }

            if ($username === '' || $email === '' || (!$isAdmin && ($dept === '' || $usn === ''))) {
                $error = 'Error updating profile!';
            } elseif ($ruleError = RegistrationRules::usernameError($username)
                ?? RegistrationRules::emailError($email)
                ?? ($isAdmin ? null : (RegistrationRules::usnError($usn) ?? RegistrationRules::departmentError($dept)))
                ?? ($newPass !== '' ? RegistrationRules::passwordError($newPass) : null)) {
                $error = $ruleError;
            } elseif ($this->users->conflictsWithOther((int) $user['id'], $username, $usn, $email)) {
                $error = 'Username, USN or email is already in use.';
            } else {
                $this->users->updateProfile(
                    (int) $user['id'],
                    $username,
                    $email,
                    $dept,
                    $usn,
                    $newPass !== '' ? Passwords::hash($newPass) : null
                );
                $_SESSION['username'] = $username;
                $_SESSION['usn'] = $usn;
                $updated = true;
            }
            $user = array_merge($user, ['username' => $username, 'email' => $email, 'dept' => $dept, 'usn' => $usn]);
        }

        View::render('profile/edit_profile', ['user' => $user, 'error' => $error, 'updated' => $updated]);
    }
}
