<?php
/**
 * 
 * @author: wallace wallaceleung@163.com
 * @date: 14-5-18
 */

import('Library.Core.Filter.Filter');
import('Library.Ext.TipsType');

class UserSignedInFilter extends Filter {

    public function doFilter($context) {

        $user = $_SESSION[SESSION_USER];

        if(empty($user)) {
            return true;
        }
        else {
            $_SESSION[TIPS] = '您已登录，不进行进行该操作';
            $_SESSION[TIPS_TYPE] = TipsType::ERROR;
            $this->_redirect(SITE_URL);
            return false;
        }
    }

}