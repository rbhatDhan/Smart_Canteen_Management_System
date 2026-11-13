<?php
require __DIR__ . '/src/bootstrap.php';
$auth = new SmartCanteen\Modules\Auth\AuthController();
SmartCanteen\Core\Http::isPost() ? $auth->adminLogin() : SmartCanteen\Core\Http::redirect("admin_login.php");
