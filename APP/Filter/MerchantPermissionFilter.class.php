<?php
/**
 * 
 * @author: wallace wallaceleung@163.com
 * @date: 14-5-18
 */

import('Library.Core.Filter.Filter');
import('Library.Ext.TipsType');
import('Model.entity.Hotel');

class MerchantPermissionFilter extends Filter {

    public function doFilter($context) {

        $isMerchant = intval($_SESSION[SESSION_USER]['isMerchant']);

        $status = true;

        if(!$isMerchant) {
            $status = false;

            $_SESSION[TIPS_TYPE] = TipsType::ERROR;
            $_SESSION[TIPS] = '您不是商家，无权限进行该操作';

            $this->_redirect(SITE_URL);

        }

        return $status;
    }

}