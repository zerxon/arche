<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-4-20
 * Time: 下午8:48
 */

import('Library.Core.Controller.IController');
import('Library.Core.View.View');

abstract class Controller implements IController {
    private $view;

    protected $module;
    protected $controller;
    protected $action;

    private function _initViewInstance() {
        if($this->view == null)
            $this->view = new View();
    }

    protected function _assign($key, $value) {
        $this->_initViewInstance();

        $this->view->assign($key, $value);
    }

    protected function _display($action = null, $module = null) {
        $this->_initViewInstance();

        if($action != null)
            $this->action = $action;

        if($module != null)
            $this->module = $module;

        $this->view->display($this->action, $this->module);
    }

    protected function _output($data, $type = null) {
        if($type == null)
            $type = C('default_output_type');

        switch($type) {
            case 'json':
                $json = json_encode($data);
                echo $json;
                break;

            case 'xml':
                // do something
                break;

            default:
                echo $data;
        }
    }

    public function _redirect($module, $action, $params = array()) {
        $url = '/'.strtolower($module).'/'.strtolower($action);

        if(is_array($params) && count($params) > 0) {
            $url .= '?';
            foreach($params as $key=>$value) {
                $url .= $key.'='.$value.'&';
            }
            $url = substr($url, 0, strlen($url) - 1);
        }

        header("location:".$url);
        exit;
    }

    public function doActionStart() {

    }

    public function doActionFinish() {

    }

    public function setController($controller) {
        $this->controller = $controller;
    }

    public function setModule($module) {
        $this->module = $module;
    }

    public function setAction($action) {
        $this->action = $action;
    }

    public abstract function index();
}