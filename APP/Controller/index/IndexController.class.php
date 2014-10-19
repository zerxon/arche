<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-4-21
 * Time: 下午10:03
 */

class IndexController extends Controller {

    public function index() {
        echo "Index page";
    }

    public function newAction() {
        //echo 'Index page new action';
        $this->_display();
    }
}