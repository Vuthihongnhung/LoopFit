<?php
namespace Core;

class Router {
    protected $routes = [];

    public function get($uri, $action) {
        $this->routes['GET'][trim($uri, '/')] = $action;
    }

    public function post($uri, $action) {
        $this->routes['POST'][trim($uri, '/')] = $action;
    }

    public function run()
    {
        $uri = $_GET['url'] ?? '';
        $this->dispatch($uri);
    }

    public function dispatch($uri)
{
    $method = $_SERVER['REQUEST_METHOD'];
    $uri = strtok($uri, '?');
    $uri = trim($uri, '/');

    foreach ($this->routes[$method] as $route => $action) {

        // convert /product/{id} -> regex
        $pattern = preg_replace('#\{[a-zA-Z0-9_]+\}#', '([0-9]+)', $route);
        $pattern = "#^" . $pattern . "$#";

        if (preg_match($pattern, $uri, $matches)) {

            array_shift($matches);

            if (is_callable($action)) {
                echo call_user_func_array($action, $matches);
                return;
            }

            $parts = explode('@', $action);
            $controllerPath = $parts[0];
            $methodName = $parts[1];

            if (strpos($controllerPath, '\\') !== false) {
                $controllerName = $controllerPath;
            } else {
                $controllerName = "App\\Controllers\\User\\$controllerPath";

                if (strpos($controllerPath, 'admin/') === 0) {
                    $controllerPath = str_replace('admin/', '', $controllerPath);
                    $controllerName = "App\\Controllers\\Admin\\$controllerPath";
                }
            }

            if (class_exists($controllerName)) {
                $controller = new $controllerName();

                if (method_exists($controller, $methodName)) {
                    echo call_user_func_array([$controller, $methodName], $matches);
                    return;
                }
            }
        }
    }

    http_response_code(404);
    echo "404 Not Found - Route: '$uri'";
}
}