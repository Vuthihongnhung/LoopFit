<?php
namespace App\Controllers\User;


use Core\Controller;

class PageController extends Controller
{
    public function about()
    {
        $this->view('user/pages/about');
    }
    public function faq()
    {
        $this->view('user/pages/faq');
    }
    public function returnPolicy()
    {
        $this->view('user/pages/return_policy');
    }
    public function shipping()
    {
        $this->view('user/pages/shipping');
    }
}