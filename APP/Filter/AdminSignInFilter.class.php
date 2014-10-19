<?php
/**
 * 
 * @author: wallace wallaceleung@163.com
 * @date: 14-5-18
 */

import('Library.Core.Filter.Filter');

class AdminSignInFilter extends Filter {

    public function doFilter() {
        return true;

        $user = $_SESSION['user'];

        if(is_object($user) && intval($user->isAdmin()) === 1) {
            return true;
        }
        else {
            $this->_redirect('Admin','signIn');
            return false;
        }
    }

}