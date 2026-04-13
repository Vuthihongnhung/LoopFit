<?php
namespace Core;

class Controller {
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

    protected function json($data) {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit();
    }

    public function view($view, $data = [])
{
    // 👉 data từ controller
    extract($data);

    // 👉 data global (cartCount, user,...)
    $shared = self::$shared ?? [];
    extract($shared);

    $viewPath = __DIR__ . "/../views/" . str_replace('.', '/', $view) . ".php";

    if (!file_exists($viewPath)) {
        die("View not found: " . $viewPath);
    }

    ob_start();
    require $viewPath;
    $content = ob_get_clean();

    // 🔥 AUTO CHỌN LAYOUT
    if (str_contains($view, 'admin')) {
        require __DIR__ . "/../views/layouts/admin.php";
    } else {
        require __DIR__ . "/../views/layouts/user.php";
    }
}

protected static $shared = [];

protected function share($key, $value)
{
    self::$shared[$key] = $value;
}

protected function getGlobalCartCount()
{
    if (!isset($_SESSION['user_id'])) return 0;

    $cart = new \App\Models\Cart();
    return $cart->countByUser($_SESSION['user_id']);
}
}
