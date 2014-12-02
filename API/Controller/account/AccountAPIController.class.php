<?php
/**
 * 
 * @author: wallace wallaceleung@163.com
 * @date: 14-11-25
 */

import('Model.service.UserService');

class AccountAPIController extends APIController {

    private $_userService;

    public function __construct() {
        $this->_userService = UserService::getInstance();
    }

    public function doSignUp() {
        $tel = trim($_POST['tel']);
        $password = trim($_POST['password']);
        $rePassword = trim($_POST['re_password']);
        $name = trim($_POST['name']);

        $status = $this->_userService->userSignUp($tel, $password, $rePassword, $name);

        if($status) {
            $this->_success = true;

            $sessionId = session_id();
            $this->_data = array(
                'sessionId' => $sessionId
            );
        }
        else {
            $this->_error = API_ERROR_TYPE::INVALID_PARAMS;
            $this->_message = '注册失败';
        }

    }

    public function doSignIn() {
        $tel = trim($_POST['signin_tel']);
        $password = trim($_POST['signin_password']);

        $success = false;

        if($tel && $password) {
            $success = $this->_userService->userSignIn($tel, $password, false);
        }

        if($success) {
            $this->_success = true;

            $sessionId = session_id();
            $this->_data = array(
                'sessionId' => $sessionId
            );
        }
        else {
            $this->_error = API_ERROR_TYPE::INVALID_PARAMS;
            $this->_message = '用户名或密码错误';
        }
    }

    public function doSignOut() {
        session_destroy();

        $this->_success = true;
    }

    public function profile() {
        $userId = intval($_SESSION[SESSION_USER]['id']);
        $user = $this->_userService->getOneById($userId);

        if(!$user) {
            $this->_error = API_ERROR_TYPE::INVALID_PARAMS;
            $this->_message = '无此用户';
        }
        else {
            $this->_success = true;
            $this->_data = $user;
        }
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
            $this->_success = true;
        }
        else {
            $this->_error = API_ERROR_TYPE::INVALID_PARAMS;
            $this->_message = '修改用户信心失败';
        }
    }

    public function doChangePassword() {
        $oldPwd = trim($_POST['oldPwd']);
        $newPwd = trim($_POST['newPwd']);
        $rePwd = trim($_POST['rePwd']);

        $userId = intval($_SESSION[SESSION_USER]['id']);
        $status = $this->_userService->changePassword($userId, $oldPwd, $newPwd, $rePwd);

        if($status) {
            $this->_success = true;
        }
        else {
            $this->_error = API_ERROR_TYPE::INVALID_PARAMS;
            $this->_message = '修改失败';
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

            $this->_success = true;
            $this->_data = array('notExist' => $notExist);
        }
        else {
            $this->_error = API_ERROR_TYPE::INVALID_PARAMS;
            $this->_message = '手机号码必须为11数字';
        }

    }

}