<?php
namespace App\Controller;

use JThink\Core\Http\Controller;

class UiController extends Controller {
    public function index() {
        $this->assign('title', 'UI Components');
        $this->display('ui');
    }
}
?>