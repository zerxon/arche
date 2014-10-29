<?php
/**
 * 
 * @author: wallace wallaceleung@163.com
 * @date: 14-10-28
 */

import('Library.Core.Model.ARModel');

class Schedule extends ARModel {

    protected $_tableName = 'schedule';

    protected $_fields = array(
        'id'=>'id',
        'orderId'=>'order_id',
        'date'=>'date'
    );

}