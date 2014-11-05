<?php
/**
 * 
 * @author: wallace wallaceleung@163.com
 * @date: 14-11-2
 */

import('Library.Core.Filter.Filter');
import('Model.entity.User');

class AutoSignInFilter extends Filter {

    public function doFilter($context) {

        if(!$_SESSION[SESSION_USER] && $_COOKIE['access_token']) {
            $accessToken = $_COOKIE['access_token'];
            list($userId, $pwd) = explode('|', $accessToken);

            if(intval($userId) > 0) {
                $user = new User();
                $user->findOne(intval($userId));

                if(!$user->isEmpty()) {
                    $password = md5(SALT.$user->password());
                    if($pwd == $password) {
                        $_SESSION[SESSION_USER] = $user->toArray();
                    }
                }
            }
        }

        return true;
    }
}