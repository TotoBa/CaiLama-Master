<?php
declare(strict_types=1);

namespace CaiLama\WebApi;

use CaiLama\WebApi\Http\Request;

final class Router
{
    private array $routes = [];

    public function get(string $path, string $controller, string $method): void
    {
        $this->routes['GET'][$path] = [$controller, $method];
    }

    public function post(string $path, string $controller, string $method): void
    {
        $this->routes['POST'][$path] = [$controller, $method];
    }

    public function dispatch(Request $request, array $config): Response
    {
        $route = $this->routes[$request->method][$request->path] ?? null;
        if ($route === null) {
            return Response::json([
                'error' => [
                    'code' => 'not_found',
                    'message' => 'Endpoint not found.',
                ],
            ], 404);
        }

        [$controllerClass, $method] = $route;
        $controller = new $controllerClass();
        return $controller->{$method}($request, $config);
    }
}
