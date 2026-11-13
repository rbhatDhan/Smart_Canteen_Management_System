<?php
require __DIR__ . '/src/bootstrap.php';
(new SmartCanteen\Modules\Orders\OrderController())->adminList();
