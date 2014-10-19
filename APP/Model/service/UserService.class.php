<?php
/**
 *
 * @author: wallace wallaceleung@163.com
 * @date: 14-5-27
 */

import('Library.Core.Model.Service');
import('Model.Entity.User');

class UserService {

    private static $_instance;

    public static function getInstance() {
        if(self::$_instance == null) {
            self::$_instance = new self;
        }

        return self::$_instance;
    }

    public function signIn($userName, $password) {
        $status = false;

        $user = new User();
        $user->findOne(array(
            'name'=>$userName,
            'password'=>$password
        ));

        if(!$user->isEmpty()) {
            $status = true;

            session_start();
            $_SESSION['user'] = $user;
        }

        return $status;
    }
}