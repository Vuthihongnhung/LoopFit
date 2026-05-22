<?php
namespace Core;

class Router
{
    protected $routes = [];
    protected $groupMiddlewares = [];

    /* ===== Thêm route GET ===== */
    public function get($uri, $action, $middlewares = [])
    {
        $this->addRoute('GET', $uri, $action, $middlewares);
    }

    /* ===== Thêm route POST ===== */
    public function post($uri, $action, $middlewares = [])
    {
        $this->addRoute('POST', $uri, $action, $middlewares);
    }

    /* ===== Thêm route PUT ===== */
    public function put($uri, $action, $middlewares = [])
    {
        $this->addRoute('PUT', $uri, $action, $middlewares);
    }

    /* ===== Thêm route DELETE ===== */
    public function delete($uri, $action, $middlewares = [])
    {
        $this->addRoute('DELETE', $uri, $action, $middlewares);
    }

    public function group($options, $callback)
    {
        $parentMiddlewares = $this->groupMiddlewares;
        if (isset($options['middleware'])) {
            $this->groupMiddlewares = array_merge($this->groupMiddlewares, (array)$options['middleware']);
        }
        // Gọi callback để đăng ký route trong nhóm
        $callback($this);
        // Khôi phục middleware cũ
        $this->groupMiddlewares = $parentMiddlewares;
    }

    /* ===== Thêm route ===== */
    protected function addRoute($method, $uri, $action, $middlewares = [])
    {
        $uri = trim($uri, '/');
        $method = strtoupper($method);

        // Gộp middleware từ group và route
        $allMiddlewares = array_merge($this->groupMiddlewares, $middlewares);

        // Chuyển {param} thành regex (hỗ trợ tên tham số)
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $uri);
        $pattern = '#^' . $pattern . '$#';

        $this->routes[$method][] = [
            'uri' => $uri,
            'pattern' => $pattern,
            'action' => $action,
            'middlewares' => $allMiddlewares
        ];
    }

    /* ===== Chạy router ===== */
    public function run()
    {
        $uri = $_GET['url'] ?? '';
        $this->dispatch($uri);
    }

    /* ===== Xử lý request, tìm route phù hợp ===== */
    public function dispatch($uri)
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = strtok($uri, '?');
        $uri = trim($uri, '/');

        if (!isset($this->routes[$method])) {
            $this->notFound($uri);
            return;
        }

        foreach ($this->routes[$method] as $route) {
            if (preg_match($route['pattern'], $uri, $matches)) {
                // Lấy các tham số có tên (không lấy chỉ số số)
                $params = array_filter($matches, function ($key) {
                    return !is_numeric($key);
                }, ARRAY_FILTER_USE_KEY);

                // Chạy middleware
                $this->runMiddlewares($route['middlewares']);

                // Gọi action
                $this->callAction($route['action'], $params);
                return;
            }
        }

        $this->notFound($uri);
    }

    /* ===== Chạy danh sách middleware ===== */
    protected function runMiddlewares($middlewares)
    {
        foreach ($middlewares as $middleware) {
            if (is_callable($middleware)) {
                call_user_func($middleware);
            } elseif (is_string($middleware) && class_exists($middleware)) {
                if (method_exists($middleware, 'handle')) {
                    call_user_func([$middleware, 'handle']);
                } elseif (method_exists($middleware, 'check')) {
                    call_user_func([$middleware, 'check']);
                } else {
                    // Nếu không có handle/check, thử tạo instance
                    $obj = new $middleware();
                    if (method_exists($obj, 'handle')) {
                        $obj->handle();
                    } elseif (method_exists($obj, 'check')) {
                        $obj->check();
                    }
                }
            }
        }
    }

    /* ===== Gọi controller action hoặc closure ===== */
    protected function callAction($action, $params)
    {
        // Nếu là Closure
        if (is_callable($action)) {
            echo call_user_func_array($action, $params);
            return;
        }

        // Dạng "ClassName@method"
        if (is_string($action) && strpos($action, '@') !== false) {
            list($class, $method) = explode('@', $action);
            $controller = $this->resolveController($class);
            if ($controller && method_exists($controller, $method)) {
                echo call_user_func_array([$controller, $method], $params);
                return;
            }
        }

        // Dạng [Controller::class, 'method']
        if (is_array($action) && count($action) === 2) {
            $controller = $this->resolveController($action[0]);
            $method = $action[1];
            if ($controller && method_exists($controller, $method)) {
                echo call_user_func_array([$controller, $method], $params);
                return;
            }
        }

        $this->notFound('');
    }

    /* ===== Phân giải tên controller thành đối tượng ===== */
    protected function resolveController($controller)
    {
        // Nếu đã là full class name (bắt đầu bằng App\...)
        if (class_exists($controller)) {
            return new $controller();
        }

        // Thử thêm namespace mặc định App\Controllers
        $fullClass = 'App\\Controllers\\' . ltrim($controller, '\\');
        if (class_exists($fullClass)) {
            return new $fullClass();
        }

        // Thử với User namespace
        $userClass = 'App\\Controllers\\User\\' . ltrim($controller, '\\');
        if (class_exists($userClass)) {
            return new $userClass();
        }

        // Thử với Admin namespace
        $adminClass = 'App\\Controllers\\Admin\\' . ltrim($controller, '\\');
        if (class_exists($adminClass)) {
            return new $adminClass();
        }

        return null;
    }

    /* ===== Trả về 404 ===== */
    protected function notFound($uri)
    {
        http_response_code(404);
        echo "404 Not Found - Route: '$uri'";
        exit;
    }
}