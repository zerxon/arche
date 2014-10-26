<?php
/**
 * 
 * @author: wallace wallaceleung@163.com
 * @date: 14-10-24
 */

import('Model.service.UserService');
import('Library.Ext.DWZJson');

class UserController extends Controller {

    private $_userService;

    public function __construct() {
        $this->_userService = UserService::getInstance();
    }

    public function all() {
        $type = $_GET['type'];
        $params = array();

        $pageIndex = intval($_POST['pageNum']);
        $pageSize = intval($_POST['numPerPage']);

        if(!$pageIndex)
            $pageIndex = 1;

        if(!$pageSize)
            $pageSize = 30;

        if($type == 'merchant')
            $params['isMerchant'] = 1;
        else
            $params['isMerchant'] = 0;

        $order = array(
            'id' => ORDER_TYPE::DESC
        );

        $usersPage = $this->_userService->getUsersByPage($pageIndex, $pageSize, $params, $order);

        if($type == 'merchant') {
            $this->_assign('type', $type);
        }

        $this->_assign('page', $usersPage);
        $this->_display('admin/user_all');
    }

    public function edit() {
        $id = intval($_GET['user_id']);

        if($id > 0) {
            $user = $this->_userService->getOneById($id);
            $this->_assign('user', $user);
        }

        $this->_display('admin/user_edit');
    }

    public function doEdit() {
        $id = intval($_POST['user_id']);

        $user = new User();

        $user->id($id);
        $user->tel($_POST['tel']);
        $user->password($_POST['password']);
        $user->name($_POST['name']);
        $user->fullName($_POST['full_name']);
        $user->otherTel($_POST['other_tel']);
        $user->isMerchant(intval($_POST['is_merchant']));
        $user->isAdmin(intval($_POST['is_admin']));

        if($id == 0) {
            $user->addTime(time());
        }

        $result = $this->_userService->save($user);

        $jsonObj = new DWZJson();

        if($result) {
            $jsonObj->statusCode = DWZStatusCode::SUCCESS;
            $jsonObj->message = '操作成功';
            $jsonObj->navTabId = 'user_all';

            if($id == 0) {
                if($user->isMerchant())
                    $jsonObj->navTabId = 'merchant_all';

                $jsonObj->callbackType = DWZCallBackType::CLOSE_CURRENT;
            }
        }
        else {
            $jsonObj->message = '操作失败';
        }

        $this->_output($jsonObj, 'json');
    }

    public function delete() {
        $id = $_GET['user_id'];
        $type = $_GET['type'];

        $status = $this->_userService->delete($id);

        $jsonObj = new DWZJson();

        if($status) {
            $jsonObj->statusCode = DWZStatusCode::SUCCESS;
            $jsonObj->message = '删除成功';

            if($type == 'merchant')
                $jsonObj->navTabId = 'merchant_all';
            else
                $jsonObj->navTabId = 'user_all';
        }
        else {
            $jsonObj->message = '删除失败';
        }

        $this->_output($jsonObj, 'json');
    }
}