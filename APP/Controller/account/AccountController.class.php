<?php
/**
 * 
 * @author: wallace wallaceleung@163.com
 * @date: 14-10-29
 */

import('Model.service.UserService');
import('Library.Ext.TipsType');

class AccountController extends Controller {

    private $_userService;

    public function __construct() {
        $this->_userService = UserService::getInstance();
    }

    public function index() {

        $this->_display('account/index');
    }

    public function doSignUp() {
        $tel = trim($_POST['tel']);
        $password = trim($_POST['password']);
        $rePassword = trim($_POST['re_password']);
        $name = trim($_POST['name']);

        $status = true;

        if(!preg_match('/^\d{11}$/', $tel))
            $status = false;
        else if($this->_userService->getOneByTel($tel))
            $status = false;

        if(mb_strlen($password, 'utf-8') < 6 || strlen($password, 'utf-8') > 16)
            $status = false;

        if($password != $rePassword)
            $status = false;

        if(mb_strlen($name, 'utf-8') < 3 || mb_strlen($name, 'utf-8') > 10)
            $status = false;

        if($status) {
            $user = new User();
            $user->tel($tel);
            $user->name($name);
            $user->password($password);
            $user->addTime(time());

            $userId = $this->_userService->save($user);
            if($userId>0) {
                $user->id($userId);
                $status = true;
            }
        }

        if($status) {
            $_SESSION[SESSION_USER] = $user->toArray();
            $this->_redirect(SITE_URL.'account');
        }
        else {
            $_SESSION[TIPS] = '注册失败';
            $_SESSION[TIPS_TYPE] = TipsType::ERROR;
            $this->_redirect(SITE_URL);
        }

    }

    public function doSignIn() {
        $tel = trim($_POST['tel']);
        $password = trim($_POST['password']);

        $success = false;

        if($tel && $password) {
            $success = $this->_userService->userSignIn($tel, $password);
        }

        if($success) {
            $this->_redirect(SITE_URL);
        }
        else {
            $_SESSION[TIPS] = '手机或者密码错误';
            $_SESSION[TIPS_TYPE] = TipsType::ERROR;
            $this->_redirect(SITE_URL);
        }
    }

    public function doSignOut() {
        unset($_SESSION[SESSION_USER]);

        $this->_redirect(SITE_URL);
    }

    public function checkExist() {
        $tel = trim($_POST['tel']);

        $notExist = false;

        if($tel && preg_match('/\d{11}/', $tel)) {
            $user = $this->_userService->getOneByTel($tel);

            if(!$user)
                $notExist = true;
        }

        if($notExist) {
            $json = array('notExist' => true);
            $this->_output($json, 'json');
        }
    }

}