<?php
/**
 * 
 * @author: wallace wallaceleung@163.com
 * @date: 14-5-18
 */

import('Library.Ext.APIFilter');

class UserSignedInFilter extends APIFilter {

    public function doFilter($context) {

        $user = $_SESSION[SESSION_USER];

        if(empty($user)) {
            return true;
        }
        else {
            $this->_error = API_ERROR_TYPE::ACCESS_FORBIDDEN;
            $this->_message = '您已登录，不进行进行该操作';

            $this->_show();
            exit;
        }
    }

}