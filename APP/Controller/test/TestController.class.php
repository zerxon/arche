<?php
/**
 * 
 * @author: wallace wallaceleung@163.com
 * @date: 14-8-22
 */

import('Model.entity.Store');

class TestController extends Controller {

    public function index() {
        echo('Test page index action');
    }

    public function all() {
        print_r($_GET);
        echo $this->module."<br />";
        echo('Test page all action');
    }
}