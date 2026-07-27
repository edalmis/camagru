<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/bootstrap.php';

$router = new Router();
$homeController = new HomeController();
$authController = new AuthController();
$imageController = new ImageController();

$router->get('/', [$homeController, 'index']);
$router->get('/register', [$authController, 'showRegister']);
$router->post('/register', [$authController, 'register']);
$router->get('/verify-email', [$authController, 'verifyEmail']);
$router->get('/login', [$authController, 'showLogin']);
$router->post('/login', [$authController, 'login']);
$router->get('/forgot-password', [$authController, 'showForgotPassword']);
$router->post('/forgot-password', [$authController, 'sendPasswordReset']);
$router->get('/reset-password', [$authController, 'showResetPassword']);
$router->post('/reset-password', [$authController, 'resetPassword']);
$router->post('/logout', [$authController, 'logout']);
$router->get('/profile', [$authController, 'profile']);
$router->post('/profile', [$authController, 'updateProfile']);
$router->post('/profile/resend-verification', [$authController, 'resendVerification']);
$router->get('/gallery', [$imageController, 'gallery']);
$router->get('/gallery/upload', [$imageController, 'showUpload']);
$router->post('/gallery/upload', [$imageController, 'storeUpload']);

$router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', $_SERVER['REQUEST_URI'] ?? '/');

