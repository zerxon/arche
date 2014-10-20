<?php
/**
 * 
 * @author: wallace wallaceleung@163.com
 * @date: 14-10-19
 */

class HotelController extends Controller {

    public function index() {
        echo 'hotel index page';
    }

    public function detail() {
        echo 'hotel detail page';
        echo '<pre>';
        print_r($_GET);
    }

    public function test() {

    }

    public function act() {
        echo 'act';
    }

}