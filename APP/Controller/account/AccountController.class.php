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

        $status = $this->_userService->userSignUp($tel, $password, $rePassword, $name);

        if($status) {
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

    public function changeProfile() {
        $userId = intval($_SESSION[SESSION_USER]['id']);
        $user = $this->_userService->getOneById($userId);

        $this->_assign('user', $user);

        $this->_display('account/change_profile');
    }

    public function changePassword() {
        $this->_display('account/change_password');
    }

    public function doChangeProfile() {
        $name = trim($_POST['name']);
        $fullName = trim($_POST['full_name']);
        $otherTel = trim($_POST['other_tel']);

        $user = new User();
        $user->id($_SESSION[SESSION_USER]['id']);
        $user->name($name);
        $user->fullName($fullName);
        $user->otherTel($otherTel);

        $status = $this->_userService->changeProfile($user);

        if($status) {
            $_SESSION[TIPS_TYPE] = TipsType::SUCCESS;
            $_SESSION[TIPS] = '修改成功';
        }
        else {
            $_SESSION[TIPS_TYPE] = TipsType::ERROR;
            $_SESSION[TIPS] = '修改失败';
        }

        $this->_redirect(SITE_URL.'account/changeProfile');
    }

    public function doChangePassword() {
        $oldPwd = trim($_POST['oldPwd']);
        $newPwd = trim($_POST['newPwd']);
        $rePwd = trim($_POST['rePwd']);

        $userId = intval($_SESSION[SESSION_USER]['id']);
        $status = $this->_userService->changePassword($userId, $oldPwd, $newPwd, $rePwd);

        if($status) {
            $_SESSION[TIPS_TYPE] = TipsType::SUCCESS;
            $_SESSION[TIPS] = '修改成功';
        }
        else {
            $_SESSION[TIPS_TYPE] = TipsType::ERROR;
            $_SESSION[TIPS] = '修改失败';
        }

        $this->_redirect(SITE_URL.'account/changePassword');
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