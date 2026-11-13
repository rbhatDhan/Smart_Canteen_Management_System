<?php
require __DIR__ . '/src/bootstrap.php';
$auth = new SmartCanteen\Modules\Auth\AuthController();
SmartCanteen\Core\Http::isPost() ? $auth->studentLogin() : SmartCanteen\Core\Http::redirect("student_login.php");
