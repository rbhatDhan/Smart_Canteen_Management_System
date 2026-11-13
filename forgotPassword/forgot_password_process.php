<?php
require __DIR__ . '/../src/bootstrap.php';
$auth = new SmartCanteen\Modules\Auth\AuthController();
SmartCanteen\Core\Http::isPost() ? $auth->forgotSubmit() : SmartCanteen\Core\Http::redirect("forgot_password.php");
