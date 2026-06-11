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
            http_response_code(404);
            renderView('errors/404', [
                'pageTitle' => 'Page not found',
                'currentUser' => currentUser(),
            ]);
            return;
        }

        $handler = $methodRoutes[$path];
        $handler();
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
}
