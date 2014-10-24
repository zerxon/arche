<?php
/**
 * 
 * @author: wallace wallaceleung@163.com
 * @date: 14-4-27
 */

import('Library.Core.Model.ARModel');

class User extends ARModel {

    protected $_tableName = 'user';

    protected  $_fields = array(
        'id'=>'id',
        'tel'=>'tel',
        'name'=>'name',
        'password'=>'password',
        'fullName'=>'full_name',
        'avatar'=>'avatar',
        'other_tel'=>'tel',
        'isMerchant'=>'is_Merchant'
    );

}