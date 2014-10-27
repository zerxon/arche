<?php
/**
 * 
 * @author: wallace wallaceleung@163.com
 * @date: 14-5-27
 */

import('Model.service.UserService');

class IndexController extends Controller {

    public function index() {
        func_get_args();
        $this->_display('admin/index');
    }

    public function signIn() {
        $this->_display('admin/sign_in');
    }

    public function doSignIn() {
        $tel = trim($_POST['tel']);
        $password = trim($_POST['password']);

        if($tel && $password) {
            $userService = UserService::getInstance();
            $status = $userService->adminSignIn($tel, $password);

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
}