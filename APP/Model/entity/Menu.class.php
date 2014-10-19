<?php
/**
 * 
 * @author: wallace wallaceleung@163.com
 * @date: 14-5-14
 */

import('Library.Core.Model.ARModel');

class Menu extends ARModel {

    protected $_tableName = 'menu';

    protected  $_fields = array(
        'id'=>'id',
        'name'=>'name',
        'blankId'=>'blank_id'
    );

}