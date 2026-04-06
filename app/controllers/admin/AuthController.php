<?php
namespace App\Controllers\Admin;

use Core\Controller;
use App\Models\User;

class AuthController extends Controller
{
    public function showLogin()
    {
        return $this->render('admin/login');
    }
    
    public function login()
    {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        
        $userModel = new User();
        $user = $userModel->getByEmail($email);
        
        if ($user && $user['role'] === 'admin' && password_verify($password, $user['password'])) {
            session_start();
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_name'] = $user['name'];
            header('Location: http://localhost:8080/LoopFit/public/index.php?url=admin/dashboard');
        } else {
            header('Location: http://localhost:8080/LoopFit/public/index.php?url=admin/login&error=1');
        }
        exit();
    }
    
    public function logout()
    {
        session_start();
        session_destroy();
        header('Location: http://localhost:8080/LoopFit/public/index.php?url=admin/login');
        exit();
    }
}