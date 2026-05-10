<?php

namespace JThink\Core\Http;

use JThink\Core\View\View;

/**
 * 基础控制器类
 * 
 * 职责：提供视图渲染、数据分配及常用的响应处理便捷方法。
 */
class Controller {
    /** @var View 视图引擎实例 */
    protected $view;
    
    public function __construct() {
        $this->view = new View();
    }
    
    /**
     * 为视图模板分配变量
     */
    protected function assign($key, $value) {
        $this->view->assign($key, $value);
    }
    
    /**
     * 渲染并显示指定的视图模板
     */
    protected function display($template) {
        $this->view->display($template);
    }
    
    /**
     * 快捷返回 JSON 响应并终止
     */
    protected function json($data) {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}