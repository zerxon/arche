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
        func_get_args();
        $this->_display('admin/index');
    }

    public function signIn() {
        $redirect = $_GET['redirect'];

        $this->_display('admin/sign_in');
    }

    public function doSignIn() {
        $tel = trim($_POST['tel']);
        $password = trim($_POST['password']);

        if($tel && $password) {
            $userService = UserService::getInstance();
            $status = $userService->signIn($tel, $password);

           if($status) {
                $this->_redirect(SITE_URL.'admin');
           }
           else {
               debug('tel or password error');
           }
        }

    }

    public function signOut() {
        session_destroy();
        $this->_redirect(SITE_URL.'admin/signIn');
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