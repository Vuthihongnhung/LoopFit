<?php
$url = $_GET['url'] ?? '';

// Load core
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/Model.php';
require_once __DIR__ . '/../core/Database.php';

// Load models cần thiết
require_once __DIR__ . '/../app/models/User.php';
require_once __DIR__ . '/../app/models/Product.php';

// Load middleware
require_once __DIR__ . '/../app/middlewares/AdminMiddleware.php';

if ($url == 'admin/login') {
    require_once __DIR__ . '/../app/controllers/admin/AuthController.php';
    $ctrl = new App\Controllers\Admin\AuthController();
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $ctrl->login();
    } else {
        $ctrl->showLogin();
    }
} elseif ($url == 'admin/dashboard') {
    require_once __DIR__ . '/../app/controllers/admin/DashboardController.php';
    $ctrl = new App\Controllers\Admin\DashboardController();
    $ctrl->index();
} elseif ($url == 'admin/products') {
    require_once __DIR__ . '/../app/controllers/admin/ProductController.php';
    $ctrl = new App\Controllers\Admin\ProductController();
    $ctrl->index();
    } elseif ($url == 'admin/api/products') {
    require_once __DIR__ . '/../app/controllers/admin/ProductController.php';
    $ctrl = new App\Controllers\Admin\ProductController();
    $ctrl->apiGetList();
} else {
    echo "404 - Route '$url' not found";
}