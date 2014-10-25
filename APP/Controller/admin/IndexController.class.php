<?php
/**
 * 
 * @author: wallace wallaceleung@163.com
 * @date: 14-5-27
 */

import('Model.service.UserService');
import('Model.service.StoreService');

class IndexController extends Controller {

    public function index() {
        $this->_display('admin/index');
    }

    public function signIn() {
        debug(base64_decode($_GET['redirect']));
        $this->_display('admin/signin');
    }

    public function doSignIn() {
        $userName = trim($_POST['username']);
        $password = trim($_POST['password']);

        $userName = 'admin';
        $password = '900903';

        if($userName && $password) {
            $userService = UserService::getInstance();
            $status = $userService->signIn($userName, $password);

           if($status) {
               debug($_SESSION['user']);
           }
        }

    }

    public function storeList() {
        $stores = StoreService::getInstance()->getAllStoresByPage(1, 10);

        $this->_assign('stores', $stores);
        $this->_assign('totalCount', 10);
        $this->_display('store_list');
    }

    public function storeEdit() {
        $storeId = intval($_GET['store_id']);

        if($storeId>0) {
            $store = StoreService::getInstance()->getStoreById($storeId);

            $this->_assign('store', $store->toArray());
            $this->_assign('is_edit', true);
        }

        $this->_display('store_edit');
    }

    public function doStoreEdit() {
        $storeId = intval($_POST['store_id']);
        if($storeId > 0) {
            $store = StoreService::getInstance()->getStoreById($storeId);
        }
        else {
            $store = new Store();
        }

        $name = $_POST['name'];

        $store->name($name);
        $store->save();

        $out = array(
            "statusCode"=>"200",
	        "message"=>"操作成功!",
            "navTabId"=>"",
	        "rel"=>"",
	        "callbackType"=>"",
	        "forwardUrl"=>"",
	        "confirmMsg"=>""
        );

        $this->_output($out, 'json');
    }
}