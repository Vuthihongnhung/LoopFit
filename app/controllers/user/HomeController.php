<?php
namespace App\Controllers\User;

use Core\Controller;

class HomeController extends Controller
{
    public function index()
{
    require_once __DIR__ . '/../../../views/user/home.php';
}
}