<?php
namespace Core;

class Router {
    protected $routes = [];

    public function get($uri, $action) {
        $this->routes['GET'][$uri] = $action;
    }

    public function post($uri, $action) {
        $this->routes['POST'][$uri] = $action;
    }

    public function dispatch($uri) {
        $method = $_SERVER['REQUEST_METHOD'];
        // Loại bỏ query string nếu có
        $uri = strtok($uri, '?');
        // Chuẩn hóa URI: xóa dấu / đầu và cuối
        $uri = trim($uri, '/');

        if (isset($this->routes[$method][$uri])) {
            $action = $this->routes[$method][$uri];
            if (is_callable($action)) {
                echo $action();
                return;
            }
            // Nếu action là "Controller@method"
            if (is_string($action)) {
                $parts = explode('@', $action);
                $controllerName = "App\\Controllers\\" . $parts[0];
                $methodName = $parts[1];
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