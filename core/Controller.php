<?php
namespace JThink\Core;

class Controller {
    protected $view;
    
    public function __construct() {
        $this->view = new View();
    }
    
    protected function assign($key, $value) {
        $this->view->assign($key, $value);
    }
    
    protected function display($template) {
        $this->view->display($template);
    }
    
    protected function json($data) {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
?>