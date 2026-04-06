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

    public function dispatch($uri) {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = strtok($uri, '?');
        $uri = trim($uri, '/');

        if (isset($this->routes[$method][$uri])) {
            $action = $this->routes[$method][$uri];

            if (is_callable($action)) {
                echo $action();
                return;
            }

            if (is_string($action)) {
                $parts = explode('@', $action);
                $controllerPath = $parts[0];
                $methodName = $parts[1];

                // xử lý namespace
                if (strpos($controllerPath, 'admin/') === 0) {
                    $controllerPath = str_replace('admin/', '', $controllerPath);
                    $controllerName = "App\\Controllers\\Admin\\$controllerPath";
                } else {
                    $controllerName = "App\\Controllers\\User\\$controllerPath";
                }

                if (class_exists($controllerName)) {
                    $controller = new $controllerName();

                    if (method_exists($controller, $methodName)) {
                        echo $controller->$methodName();
                        return;
                    }
                }
            }
        }

        http_response_code(404);
        echo "404 Not Found - Route: '$uri'";
    }
}