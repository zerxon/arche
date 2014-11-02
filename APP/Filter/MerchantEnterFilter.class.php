<?php
/**
 * 
 * @author: wallace wallaceleung@163.com
 * @date: 14-5-18
 */

import('Library.Core.Filter.Filter');
import('Library.Ext.TipsType');
import('Model.entity.Hotel');

class MerchantEnterFilter extends Filter {

    public function doFilter($context) {

        $userId = intval($_SESSION[SESSION_USER]['id']);

        $hotel = new Hotel();
        $hotel->findOne(array('userId'=>$userId));

        $status = true;

        if(!$hotel->isEmpty()) {
            if($hotel->status() == 0) {
                $_SESSION[TIPS_TYPE] = TipsType::WARNING;
                $_SESSION[TIPS] = '您已申请，请耐心等候审核';
            }
            else if($hotel->status() == 1) {
                $_SESSION[TIPS_TYPE] = TipsType::WARNING;
                $_SESSION[TIPS] = '您已经是商家，不能进行该操作';
            }

            $this->_redirect(SITE_URL);

            $status = false;
        }

        return $status;
    }

}