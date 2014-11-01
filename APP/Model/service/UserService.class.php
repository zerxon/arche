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

    public function getOneByTel($tel) {
        $params = array(
            'tel' => $tel
        );

        $user = new User();
        $user->findOne($params);

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

    public function userSignIn($tel, $password) {
        $status = false;

        $user = new User();
        $user->findOne(array(
            'tel'=>$tel,
            'password'=>$password
        ));

        if(!$user->isEmpty()) {
            $status = true;
            $_SESSION[SESSION_USER] = $user->toArray();
        }

        return $status;
    }

    public function userSignUp($tel ,$password, $rePassword, $name) {
        $status = true;

        if(!preg_match('/^\d{11}$/', $tel))
            $status = false;
        else if($this->_userService->getOneByTel($tel))
            $status = false;

        if(mb_strlen($password, 'utf-8') < 6 || strlen($password, 'utf-8') > 16)
            $status = false;
        else if($password != $rePassword)
            $status = false;

        if(mb_strlen($name, 'utf-8') < 2 || mb_strlen($name, 'utf-8') > 10)
            $status = false;

        if($status) {
            $user = new User();
            $user->tel($tel);
            $user->name($name);
            $user->password($password);
            $user->addTime(time());

            $userId = $this->save($user);
            if($userId>0) {
                $status = true;

                $user->id($userId);
                $_SESSION[SESSION_USER] = $user->toArray();
            }
        }

        return $status;
    }

    public function changeProfile($user) {
        $status = true;

        $name = $user->name();
        $fullName = $user->fullName();
        $otherTel = $user->otherTel();

        if(mb_strlen($name, 'utf-8') < 2 || mb_strlen($name, 'utf-8') > 10)
            $status = false;

        if($fullName && (mb_strlen($fullName, 'utf-8') < 2 || mb_strlen($fullName, 'utf-8') > 16))
            $status = false;

        if(strlen($otherTel) == 0)
            $status = false;

        if($status && intval($user->id()) > 0) {
            $status = $user->save();
        }

        return $status;
    }

    public function changePassword($userId, $oldPwd, $newPwd, $rePwd) {
        $status = true;

        if(mb_strlen($oldPwd, 'utf-8') < 6 || strlen($oldPwd, 'utf-8') > 16)
            $status = false;

        if(mb_strlen($newPwd, 'utf-8') < 6 || strlen($newPwd, 'utf-8') > 16)
            $status = false;
        else if($newPwd != $rePwd)
            $status = false;

        if($userId == 0)
            $status = false;

        if($status) {
            $status = $this->_userORM->update()
                ->field('password')->eq($newPwd)
                ->where()->field('id')->eq($userId)
                ->andField('password')->eq($oldPwd)
                ->execute();
        }

        return $status;
    }

    public function validate($user) {

    }

}