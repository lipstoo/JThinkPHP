<?php
namespace App\Controller;

use JThink\Core\Http\Controller;

class HomeController extends Controller {
    public function index() {
        $this->assign('title', 'JThinkPHP');
        $this->assign('desc', 'A minimalist PHP framework');
        $this->display('home');
    }
    
    public function home() {
        $this->assign('title', 'Home');
        $this->display('home');
    }
}
?>