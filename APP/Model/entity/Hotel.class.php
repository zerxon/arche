<?php
/**
 * 
 * @author: wallace wallaceleung@163.com
 * @date: 14-10-21
 */

import('Library.Core.Model.ARModel');
import('Model.entity.User');

Class Hotel extends ARModel {
    protected $_tableName = 'hotel';

    //模型字段的主键, 默认为id
    //protected $_primaryKey = 'id';

    protected $_fields = array(
        'id'=>'id',
        'name'=>'name',
        'tel'=>'tel',
        'address'=>'address',
        'isOpening'=>'is_opening',
        'status'=>'status',
        'addTime'=>'add_time',
        'userId'=>'user_id'
    );

    protected $_mappers = array(
        'user'=>array(
            'type'=>'hasOne',
            'fetch'=>FetchType::LAZY,
            'target'=>'User',
            'mapping'=>array(
                'userId'=>'id'
            )
        )
    );
}