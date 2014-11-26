<?php
/**
 * 
 * @author: wallace wallaceleung@163.com
 * @date: 14-10-21
 */

import('Library.Core.Model.ARModel');
import('Model.entity.User');
import('Model.entity.Room');

class Hotel extends ARModel {
    protected $_tableName = 'hotel';

    //模型字段的主键, 默认为id
    //protected $_primaryKey = 'id';

    protected $_fields = array(
        'id'=>'id',
        'name'=>'name',
        'logo'=>'logo',
        'tel'=>'tel',
        'otherTel'=>'other_tel',
        'address'=>'address',
        'isOpening'=>'is_opening',
        'status'=>'status',
        'desc'=>'desc',
        'addTime'=>'add_time',
        'userId'=>'user_id'
    );

    protected $_mappers = array(
        'user'=>array(
            'Type'=>'hasOne',
            'Fetch'=>FetchType::LAZY,
            'Target'=>'User',
            'Mapping'=>array(
                'userId'=>'id'
            )
        ),
        'rooms'=>array(
            'Type'=>'hasMany',
            'Fetch'=>FetchType::LAZY,
            'Target'=>'Room',
            'Order'=>'id desc', //默认排序
            'Mapping'=>array(
                'id'=>'hotelId'
            )
        )
    );

}