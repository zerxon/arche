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
        'name'=>'name',
        'password'=>'password',
        'phone'=>'phone',
        'isAdmin'=>'is_admin'
    );

}