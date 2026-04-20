<?php
namespace Core;

class Controller {
    protected static $shared = [];

    protected function share($key, $value) {
        self::$shared[$key] = $value;
    }

    protected function render($view, $data = []) {
        // Giải nén mảng thành các biến để dùng ở View
        extract($data);

        // __DIR__ là thư mục app/core, nên ta lùi 1 cấp rồi vào views
        $viewPath = dirname(__DIR__) . '/views/' . $view . '.php';

        if (file_exists($viewPath)) {
            require_once $viewPath;
        } else {
            echo "Lỗi: Không tìm thấy file view tại: " . $viewPath;
        }
    }

    public function view($view, $data = []) {
        if (!isset(self::$shared['cartCount'])) {
            $this->share('cartCount', $this->getGlobalCartCount());
        }

        $allData = array_merge(self::$shared, $data);
        extract($allData);

        $viewPath = __DIR__ . "/../views/" . str_replace('.', '/', $view) . ".php";
        if (!file_exists($viewPath)) {
            die("View not found: " . $viewPath);
        }

        ob_start();
        require $viewPath;
        $content = ob_get_clean();

        if (strpos($view, 'admin') === 0 || strpos($view, '/admin') !== false) {
            $layoutPath = __DIR__ . "/../views/layouts/admin.php";
        } else {
            $layoutPath = __DIR__ . "/../views/layouts/user.php";
        }

        if (file_exists($layoutPath)) {
            require $layoutPath;
        } else {
            echo $content;
        }
    }

    protected function json($data) {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit();
    }

    protected function getGlobalCartCount() {
        if (!isset($_SESSION['user']['id'])) {
            return 0;
        }
        $cartModel = new \App\Models\Cart();
        if (method_exists($cartModel, 'countByUser')) {
            return $cartModel->countByUser($_SESSION['user']['id']);
        }
        $cart = $_SESSION['cart'] ?? [];
        return array_sum(array_column($cart, 'quantity'));
    }
}