<?php
/**
 * 
 * @author: wallace wallaceleung@163.com
 * @date: 14-10-29
 */

import('Model.service.UserService');
import('Model.service.OrderService');
import('Library.Ext.TipsType');

class AccountController extends Controller {

    private $_userService;

    public function __construct() {
        $this->_userService = UserService::getInstance();
    }

    public function index() {

        if($_SESSION[SESSION_USER]['isMerchant'])
            $this->_redirect(SITE_URL.'account/merchant/hotelCenter');
        else
            $this->_redirect(SITE_URL.'account/myOrder');
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
        $tel = trim($_POST['signin_tel']);
        $password = trim($_POST['signin_password']);
        $keep = intval($_POST['keep']);

        $success = false;

        if($tel && $password) {
            $success = $this->_userService->userSignIn($tel, $password, $keep);
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
        //unset($_SESSION[SESSION_USER]);

        session_destroy();
        setcookie('access_token', '', -1, '/');

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

        $notExist = 'false';

        if($tel && preg_match('/\d{11}/', $tel)) {
            $user = $this->_userService->getOneByTel($tel);

            if(!$user)
                $notExist = 'true';
        }

        $this->_output($notExist);
    }

    public function myOrder() {
        $userId = intval($_SESSION[SESSION_USER]['id']);

        $params = array(
            'userId' => $userId,
            'isUserIgnore'=>0
        );

        $order = array(
            'id' => 'desc'
        );

        $orderService = OrderService::getInstance();
        $page = $orderService->getOrdersByPage(1, 10, $params, $order);

        if($page['records']) {
            foreach($page['records'] as $index => $record) {
                $strDate = substr($record['range'], 0, strpos($record['range'], '('));
                $date = strtotime($strDate);
                $date += 86400;
                if($date < time())
                    $page['records'][$index]['isExpiry'] =  true;
                else
                    $page['records'][$index]['isExpiry'] = false;
            }
        }

        $this->_assign('page', $page);
        $this->_display('account/my_order');
    }

    public function cancelOrder() {
        $orderId = intval($_GET['order_id']);
        $userId = intval($_SESSION[SESSION_USER]['id']);

        $status = true;
        if($orderId < 1)
            $status = false;

        if($status) {
            $orderService = OrderService::getInstance();
            $status = $orderService->cancelOrderByIdAndUserId($orderId, $userId);
        }

        if($status) {
            $_SESSION[TIPS_TYPE] = TipsType::SUCCESS;
            $_SESSION[TIPS] = '取消订单成功';
        }
        else {
            $_SESSION[TIPS_TYPE] = TipsType::ERROR;
            $_SESSION[TIPS] = '取消订单失败';
        }

        $this->_redirect(SITE_URL.'account/myOrder');
    }

    public function ignoreOrder() {
        $orderId = intval($_GET['order_id']);
        $userId = intval($_SESSION[SESSION_USER]['id']);

        $status = true;
        if($orderId < 1)
            $status = false;

        if($status) {
            $orderService = OrderService::getInstance();
            $status = $orderService->ignoreOrderByIdAndUserId($orderId, $userId);
        }

        if($status) {
            $_SESSION[TIPS_TYPE] = TipsType::SUCCESS;
            $_SESSION[TIPS] = '删除订单成功';
        }
        else {
            $_SESSION[TIPS_TYPE] = TipsType::ERROR;
            $_SESSION[TIPS] = '删除订单失败';
        }

        $this->_redirect(SITE_URL.'account/myOrder');
    }

}