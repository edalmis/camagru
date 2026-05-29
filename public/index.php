<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/bootstrap.php';

$router = new Router();
$homeController = new HomeController();
$authController = new AuthController();

$router->get('/', [$homeController, 'index']);
$router->get('/register', [$authController, 'showRegister']);
$router->post('/register', [$authController, 'register']);
$router->get('/login', [$authController, 'showLogin']);
$router->post('/login', [$authController, 'login']);
$router->post('/logout', [$authController, 'logout']);
$router->get('/profile', [$authController, 'profile']);

$router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', $_SERVER['REQUEST_URI'] ?? '/');

