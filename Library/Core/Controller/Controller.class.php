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
    private $_view;

    protected $_module;
    protected $_action;

    private function _initViewInstance() {
        if($this->_view == null)
            $this->_view = new View();
    }

    protected function _assign($key, $value) {
        $this->_initViewInstance();

        $this->_view->assign($key, $value);
    }

    protected function _display($path) {
        $this->_initViewInstance();

        $this->_view->display($path);
    }

    protected function _output($data, $type = null) {
        $type = strtolower($type);

        switch($type) {
            case OUTPUT_TYPE::JSON:
                header('Content-Type:application/json');
                $json = json_encode($data);
                echo $json;
                break;

            case OUTPUT_TYPE::XML:
                // do something
                break;

            default:
                echo $data;
        }
    }

    public function _redirect($url) {
        header("Location:".$url);
        exit;
    }

    public function doActionStart() {

    }

    public function doActionFinish() {

    }

    public function setModule($module) {
        $this->_module = $module;
        define('MODULE', $module);
    }

    public function setAction($action) {
        $this->_action = $action;
        define('ACTION', $action);
    }

    //public abstract function index();
}

class OUTPUT_TYPE {
    const JSON = 'json';
    const XML = 'xml';
}