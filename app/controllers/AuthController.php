<?php

declare(strict_types=1);

final class AuthController
{
    public function showRegister(): void
    {
        if (currentUser() !== null) {
            redirectTo('/profile');
        }

        renderView('auth/register', [
            'pageTitle' => 'Register',
            'error' => getFlash('error'),
            'old' => $_SESSION['old'] ?? [],
        ]);

        unset($_SESSION['old']);
    }

    public function register(): void
    {
        if (currentUser() !== null) {
            redirectTo('/profile');
        }

        $username = trim((string) ($_POST['username'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $confirmPassword = (string) ($_POST['password_confirmation'] ?? '');

        $_SESSION['old'] = [
            'username' => $username,
            'email' => $email,
        ];

        $errors = $this->validateRegistration($username, $email, $password, $confirmPassword);

        if (!empty($errors)) {
            renderView('auth/register', [
                'pageTitle' => 'Register',
                'errors' => $errors,
                'old' => $_SESSION['old'],
            ]);
            unset($_SESSION['old']);
            return;
        }

        $userModel = new User();

        if ($userModel->findByUsername($username) !== null) {
            renderView('auth/register', [
                'pageTitle' => 'Register',
                'errors' => ['Username is already taken.'],
                'old' => $_SESSION['old'],
            ]);
            unset($_SESSION['old']);
            return;
        }

        if ($userModel->findByEmail($email) !== null) {
            renderView('auth/register', [
                'pageTitle' => 'Register',
                'errors' => ['Email is already registered.'],
                'old' => $_SESSION['old'],
            ]);
            unset($_SESSION['old']);
            return;
        }

        $userId = $userModel->create($username, $email, $password);
        $user = $userModel->findById($userId);

        if ($user === null) {
            setFlash('error', 'Registration failed. Please try again.');
            redirectTo('/register');
        }

        loginUser($user);
        unset($_SESSION['old']);
        setFlash('success', 'Account created successfully.');
        redirectTo('/profile');
    }

    public function showLogin(): void
    {
        if (currentUser() !== null) {
            redirectTo('/profile');
        }

        renderView('auth/login', [
            'pageTitle' => 'Login',
            'error' => getFlash('error'),
            'success' => getFlash('success'),
            'old' => $_SESSION['old'] ?? [],
        ]);

        unset($_SESSION['old']);
    }

    public function login(): void
    {
        if (currentUser() !== null) {
            redirectTo('/profile');
        }

        $email = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        $_SESSION['old'] = ['email' => $email];

        if ($email === '' || $password === '') {
            renderView('auth/login', [
                'pageTitle' => 'Login',
                'errors' => ['Email and password are required.'],
                'old' => $_SESSION['old'],
            ]);
            unset($_SESSION['old']);
            return;
        }

        $user = (new User())->findByEmail($email);

        if ($user === null || !password_verify($password, $user['password_hash'])) {
            renderView('auth/login', [
                'pageTitle' => 'Login',
                'errors' => ['Invalid email or password.'],
                'old' => $_SESSION['old'],
            ]);
            unset($_SESSION['old']);
            return;
        }

        loginUser($user);
        unset($_SESSION['old']);
        setFlash('success', 'Welcome back.');
        redirectTo('/profile');
    }

    public function logout(): void
    {
        if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            setFlash('error', 'Invalid logout request. Please try again.');
            redirectTo('/profile');
        }

        logoutUser();
        redirectTo('/login');
    }

    public function profile(): void
    {
        $user = requireAuthentication();

        renderView('profile/show', [
            'pageTitle' => 'Profile',
            'currentUser' => $user,
            'success' => getFlash('success'),
            'error' => getFlash('error'),
        ]);
    }

    private function validateRegistration(string $username, string $email, string $password, string $confirmPassword): array
    {
        $errors = [];

        if ($username === '' || strlen($username) < 3 || strlen($username) > 30) {
            $errors[] = 'Username must be between 3 and 30 characters.';
        }

        if ($email === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $errors[] = 'Please enter a valid email address.';
        }

        if (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters long.';
        }

        if ($password !== $confirmPassword) {
            $errors[] = 'Password confirmation does not match.';
        }

        return $errors;
    }
}
