<?php

declare(strict_types=1);

final class Router
{
    private array $routes = [];

    public function get(string $path, callable $handler): void
    {
        $this->routes['GET'][$this->normalizePath($path)] = $handler;
    }

    public function post(string $path, callable $handler): void
    {
        $this->routes['POST'][$this->normalizePath($path)] = $handler;
    }

    public function dispatch(string $method, string $requestUri): void
    {
        $path = $this->normalizeRequestPath($requestUri);
        $methodRoutes = $this->routes[$method] ?? [];

        if (!isset($methodRoutes[$path])) {
            if ($this->routeExistsForOtherMethod($path, $method)) {
                renderErrorPage(405, 'errors/405', [
                    'pageTitle' => 'Method not allowed',
                    'currentUser' => currentUser(),
                ]);
                return;
            }

            renderErrorPage(404, 'errors/404', [
                'pageTitle' => 'Page not found',
                'currentUser' => currentUser(),
            ]);
            return;
        }

        try {
            $handler = $methodRoutes[$path];
            $handler();
        } catch (Throwable $exception) {
            error_log('Unhandled route exception: ' . $exception->getMessage());
            renderErrorPage(500, 'errors/500', [
                'pageTitle' => 'Server error',
                'currentUser' => currentUser(),
            ]);
        }
    }

    private function normalizePath(string $path): string
    {
        $normalizedPath = '/' . trim($path, '/');

        return $normalizedPath === '/' ? '/' : rtrim($normalizedPath, '/');
    }

    private function normalizeRequestPath(string $requestUri): string
    {
        $path = parse_url($requestUri, PHP_URL_PATH) ?: '/';

        return $this->normalizePath($path);
    }

    private function routeExistsForOtherMethod(string $path, string $currentMethod): bool
    {
        foreach ($this->routes as $method => $routes) {
            if ($method === $currentMethod) {
                continue;
            }

            if (isset($routes[$path])) {
                return true;
            }
        }

        return false;
    }
}
