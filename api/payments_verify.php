<?php
require __DIR__ . '/../src/bootstrap.php';
(new SmartCanteen\Modules\Payments\PaymentController())->verify();
