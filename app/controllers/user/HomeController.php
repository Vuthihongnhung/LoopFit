<?php
namespace App\Controllers\User;

use Core\Controller;

class HomeController extends Controller
{

public function index()
{
    $this->share('cartCount', $this->getGlobalCartCount());

    return $this->view('user/home');
}
}