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

        try {
            $user = (new User())->register($username, $email, $password, $confirmPassword);
            loginUser($user);
            unset($_SESSION['old']);
            setFlash('success', 'Account created successfully.');
            redirectTo('/profile');
        } catch (ValidationException $exception) {
            renderView('auth/register', [
                'pageTitle' => 'Register',
                'errors' => $exception->getErrors(),
                'old' => $_SESSION['old'],
            ]);
            unset($_SESSION['old']);
            return;
        } catch (Throwable $exception) {
            error_log('Registration failed: ' . $exception->getMessage());
            setFlash('error', 'Registration failed. Please try again.');
            redirectTo('/register');
        }
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

        try {
            $user = (new User())->authenticate($email, $password);
            loginUser($user);
            unset($_SESSION['old']);
            setFlash('success', 'Welcome back.');
            redirectTo('/profile');
        } catch (ValidationException $exception) {
            renderView('auth/login', [
                'pageTitle' => 'Login',
                'errors' => $exception->getErrors(),
                'old' => $_SESSION['old'],
            ]);
            unset($_SESSION['old']);
            return;
        } catch (Throwable $exception) {
            error_log('Login failed: ' . $exception->getMessage());
            setFlash('error', 'Unable to log in right now. Please try again.');
            redirectTo('/login');
        }
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

}
