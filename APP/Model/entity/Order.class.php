<?php
/**
 * 
 * @author: wallace wallaceleung@163.com
 * @date: 14-10-27
 */

import('Library.Core.Model.ARModel');
import('Model.entity.Room');
import('Model.entity.Hotel');
import('Model.entity.User');

class Order extends ARModel {

    protected $_tableName = 'order';

    protected $_fields = array(
        'id'=>'id',
        'roomId'=>'room_id',
        'hotelId'=>'hotel_id',
        'userId'=>'user_id',
        'code'=>'code',
        'comment'=>'comment',
        'status'=>'status',
        'addTime'=>'add_time'
    );

    protected $_mappers = array(
        'room'=>array(
            'Type'=>'hasOne',
            'Fetch'=>FetchType::LAZY,
            'Target'=>'Room',
            'Mapping'=>array(
                'roomId'=>'id'
            ),
        ),
        'hotel'=>array(
            'Type'=>'hasOne',
            'Fetch'=>FetchType::LAZY,
            'Target'=>'Hotel',
            'Mapping'=>array(
                'hotelId'=>'id'
            ),
        ),
        'user'=>array(
            'Type'=>'hasOne',
            'Fetch'=>FetchType::LAZY,
            'Target'=>'User',
            'Mapping'=>array(
                'UserId'=>'id'
            ),
        )
    );

}