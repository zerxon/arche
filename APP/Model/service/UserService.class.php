<?php
/**
 *
 * @author: wallace wallaceleung@163.com
 * @date: 14-5-27
 */

import('Model.Entity.User');

class UserService {

    private static $_instance;
    private $_userORM;

    private function __construct() {
        $this->_userORM = new ORM('User');
    }

    public static function getInstance() {
        if(self::$_instance == null) {
            self::$_instance = new self;
        }

        return self::$_instance;
    }

    public function getUsersByPage($pageIndex, $pageSize, $params = null, $order = null) {

        $orm = $this->_userORM->selectAll();

        if(is_array($params) && count($params) > 0)
            $orm = $orm->where($params);

        $usersPage = $orm->orderBy($order)
            ->queryPage($pageIndex, $pageSize);

        return $usersPage;
    }

    public function getOneById($id) {
        $user = new User();
        $user->findOne($id);

        return $user->toArray();
    }

    public function save($user) {
        return $user->save();
    }

    public function delete($id) {

        if(intval($id) < 1)
            return false;

        $status = $this->_userORM->delete()
            ->where()
            ->field('id')
            ->eq($id)
            ->execute();

        return $status;
    }

    public function adminSignIn($tel, $password) {
        $status = false;

        $user = new User();
        $user->findOne(array(
            'tel'=>$tel,
            'password'=>$password
        ));

        if(!$user->isEmpty() && $user->isAdmin()) {
            $status = true;
            $_SESSION[SESSION_ADMIN] = $user->toArray();
        }

        return $status;
    }
}