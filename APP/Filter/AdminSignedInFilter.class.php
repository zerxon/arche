<?php
/**
 * 
 * @author: wallace wallaceleung@163.com
 * @date: 14-5-18
 */

import('Library.Core.Filter.Filter');

class AdminSignedInFilter extends Filter {

    public function doFilter($context) {

        $user = $_SESSION[SESSION_ADMIN];

        if(empty($user)) {
            return true;
        }
        else {
            $this->_redirect(SITE_URL.'admin');
            return false;
        }
    }

}