<?php
/**
 * 
 * @author: wallace wallaceleung@163.com
 * @date: 14-5-18
 */

import('Library.Core.Filter.Filter');
import('Library.Ext.TipsType');

class UserPermissionFilter extends Filter {

    public function doFilter($context) {

        $user = $_SESSION[SESSION_USER];

        if(!empty($user)) {
            return true;
        }
        else {
            $_SESSION[TIPS] = '您无权限访问，请先登录';
            $_SESSION[TIPS_TYPE] = TipsType::ERROR;

            if(strpos($_SERVER['HTTP_REFERER'], SITE_URL) > -1)
                $this->_redirect($_SERVER['HTTP_REFERER']);
            else
                $this->_redirect(SITE_URL);

            return false;
        }
    }

}