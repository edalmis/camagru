<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/bootstrap.php';

$router = new Router();
$homeController = new HomeController();

$router->get('/', [$homeController, 'index']);

$router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', $_SERVER['REQUEST_URI'] ?? '/');
