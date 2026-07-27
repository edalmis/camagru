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
            'csrfToken' => csrfToken(),
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

        if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            setFlash('error', 'Invalid form token. Please try again.');
            redirectTo('/register');
        }

        try {
            $userModel = new User();
            $user = $userModel->register($username, $email, $password, $confirmPassword);
            $verificationToken = $userModel->issueAuthToken((int) $user['id'], 'email_verify', 86400);
            $verificationLink = appBaseUrl() . '/verify-email?token=' . urlencode($verificationToken);

            sendMailMessage(
                $user['email'],
                'Confirm your Camagru account',
                "Hello {$user['username']},\n\nPlease confirm your account by opening this link:\n{$verificationLink}\n\nIf you did not create this account, you can ignore this message."
            );

            unset($_SESSION['old']);
            setFlash('success', 'Account created. Check your email to confirm your account before logging in.');
            redirectTo('/login');
        } catch (ValidationException $exception) {
            renderView('auth/register', [
                'pageTitle' => 'Register',
                'errors' => $exception->getErrors(),
                'old' => $_SESSION['old'],
                'csrfToken' => csrfToken(),
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
            'csrfToken' => csrfToken(),
        ]);

        unset($_SESSION['old']);
    }

    public function login(): void
    {
        if (currentUser() !== null) {
            redirectTo('/profile');
        }

        $username = trim((string) ($_POST['username'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        $_SESSION['old'] = ['username' => $username];

        if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            setFlash('error', 'Invalid form token. Please try again.');
            redirectTo('/login');
        }

        try {
            $user = (new User())->authenticate($username, $password);
            loginUser($user);
            unset($_SESSION['old']);
            setFlash('success', 'Welcome back.');
            redirectTo('/profile');
        } catch (ValidationException $exception) {
            renderView('auth/login', [
                'pageTitle' => 'Login',
                'errors' => $exception->getErrors(),
                'old' => $_SESSION['old'],
                'csrfToken' => csrfToken(),
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
            'errors' => [],
            'csrfToken' => csrfToken(),
        ]);
    }

    public function updateProfile(): void
    {
        $currentUser = requireAuthentication();

        if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            setFlash('error', 'Invalid profile form token. Please try again.');
            redirectTo('/profile');
        }

        $username = trim((string) ($_POST['username'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $currentPassword = (string) ($_POST['current_password'] ?? '');
        $newPassword = (string) ($_POST['new_password'] ?? '');
        $confirmPassword = (string) ($_POST['new_password_confirmation'] ?? '');

        try {
            $userModel = new User();
            $result = $userModel->updateProfile((int) $currentUser['id'], $username, $email, $currentPassword, $newPassword, $confirmPassword);
            loginUser($result['user']);

            if ($result['emailChanged']) {
                $verificationToken = $userModel->issueAuthToken((int) $result['user']['id'], 'email_verify', 86400);
                $verificationLink = appBaseUrl() . '/verify-email?token=' . urlencode($verificationToken);
                sendMailMessage(
                    $result['user']['email'],
                    'Confirm your new Camagru email address',
                    "Hello {$result['user']['username']},\n\nPlease confirm your new email address by opening this link:\n{$verificationLink}\n\nIf you did not request this change, please ignore this message."
                );

                setFlash('success', 'Profile updated. Check your new email address to confirm it.');
            } else {
                setFlash('success', 'Profile updated successfully.');
            }

            redirectTo('/profile');
        } catch (ValidationException $exception) {
            renderView('profile/show', [
                'pageTitle' => 'Profile',
                'currentUser' => $currentUser,
                'success' => getFlash('success'),
                'error' => getFlash('error'),
                'errors' => $exception->getErrors(),
                'csrfToken' => csrfToken(),
            ]);
            return;
        } catch (Throwable $exception) {
            error_log('Profile update failed: ' . $exception->getMessage());
            setFlash('error', 'Unable to update your profile right now.');
            redirectTo('/profile');
        }
    }

    public function verifyEmail(): void
    {
        $token = (string) ($_GET['token'] ?? '');
        $tokenRecord = (new User())->consumeAuthToken('email_verify', $token);

        if ($tokenRecord === null) {
            setFlash('error', 'This confirmation link is invalid or has expired.');
            redirectTo('/login');
        }

        (new User())->markEmailVerified((int) $tokenRecord['user_id']);
        setFlash('success', 'Your account has been confirmed. You can now log in.');
        redirectTo('/login');
    }

    public function showForgotPassword(): void
    {
        if (currentUser() !== null) {
            redirectTo('/profile');
        }

        renderView('auth/forgot_password', [
            'pageTitle' => 'Forgot Password',
            'success' => getFlash('success'),
            'error' => getFlash('error'),
            'csrfToken' => csrfToken(),
            'old' => $_SESSION['old'] ?? [],
        ]);
    }

    public function sendPasswordReset(): void
    {
        if (currentUser() !== null) {
            redirectTo('/profile');
        }

        if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            setFlash('error', 'Invalid form token. Please try again.');
            redirectTo('/forgot-password');
        }

        $email = trim((string) ($_POST['email'] ?? ''));
        $_SESSION['old'] = ['email' => $email];

        try {
            $userModel = new User();
            $resetToken = $userModel->requestPasswordReset($email);

            if ($resetToken !== null) {
                $resetLink = appBaseUrl() . '/reset-password?token=' . urlencode($resetToken);
                $recipient = $userModel->findByEmail($email);

                if ($recipient !== null) {
                    sendMailMessage(
                        $recipient['email'],
                        'Reset your Camagru password',
                        "Hello {$recipient['username']},\n\nOpen this link to choose a new password:\n{$resetLink}\n\nIf you did not request a reset, you can ignore this message."
                    );
                }
            }

            unset($_SESSION['old']);
            setFlash('success', 'If that email address exists, a password reset message has been sent.');
            redirectTo('/login');
        } catch (Throwable $exception) {
            error_log('Password reset request failed: ' . $exception->getMessage());
            setFlash('error', 'Unable to process the password reset request right now.');
            redirectTo('/forgot-password');
        }
    }

    public function showResetPassword(): void
    {
        if (currentUser() !== null) {
            redirectTo('/profile');
        }

        renderView('auth/reset_password', [
            'pageTitle' => 'Reset Password',
            'success' => getFlash('success'),
            'error' => getFlash('error'),
            'csrfToken' => csrfToken(),
            'token' => (string) ($_GET['token'] ?? ''),
        ]);
    }

    public function resetPassword(): void
    {
        if (currentUser() !== null) {
            redirectTo('/profile');
        }

        if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            setFlash('error', 'Invalid form token. Please try again.');
            redirectTo('/forgot-password');
        }

        $token = (string) ($_POST['token'] ?? '');
        $newPassword = (string) ($_POST['password'] ?? '');
        $confirmPassword = (string) ($_POST['password_confirmation'] ?? '');

        if ($newPassword !== $confirmPassword) {
            renderView('auth/reset_password', [
                'pageTitle' => 'Reset Password',
                'error' => 'Password confirmation does not match.',
                'csrfToken' => csrfToken(),
                'token' => $token,
            ]);
            return;
        }

        if (strlen($newPassword) < 8) {
            renderView('auth/reset_password', [
                'pageTitle' => 'Reset Password',
                'error' => 'Password must be at least 8 characters long.',
                'csrfToken' => csrfToken(),
                'token' => $token,
            ]);
            return;
        }

        try {
            $userModel = new User();
            $tokenRecord = $userModel->consumeAuthToken('password_reset', $token);

            if ($tokenRecord === null) {
                setFlash('error', 'This reset link is invalid or has expired.');
                redirectTo('/forgot-password');
            }

            $user = $userModel->findById((int) $tokenRecord['user_id']);

            if ($user === null) {
                throw new RuntimeException('Unable to load user for password reset.');
            }

            $userModel->updatePasswordById((int) $user['id'], $newPassword);

            setFlash('success', 'Your password has been reset. You can log in now.');
            redirectTo('/login');
        } catch (Throwable $exception) {
            error_log('Password reset failed: ' . $exception->getMessage());
            setFlash('error', 'Unable to reset your password right now.');
            redirectTo('/forgot-password');
        }
    }

    public function resendVerification(): void
    {
        $user = requireAuthentication();

        if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            setFlash('error', 'Invalid profile form token. Please try again.');
            redirectTo('/profile');
        }

        try {
            $userModel = new User();
            $verificationToken = $userModel->issueAuthToken((int) $user['id'], 'email_verify', 86400);
            $verificationLink = appBaseUrl() . '/verify-email?token=' . urlencode($verificationToken);

            sendMailMessage(
                $user['email'],
                'Confirm your Camagru account',
                "Hello {$user['username']},\n\nPlease confirm your account by opening this link:\n{$verificationLink}\n\nIf you did not create this account, you can ignore this message."
            );

            setFlash('success', 'A new confirmation email has been sent.');
            redirectTo('/profile');
        } catch (Throwable $exception) {
            error_log('Resend verification failed: ' . $exception->getMessage());
            setFlash('error', 'Unable to resend the confirmation email right now.');
            redirectTo('/profile');
        }
    }

}
