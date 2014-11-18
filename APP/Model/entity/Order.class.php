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
import('Model.entity.Schedule');

class Order extends ARModel {

    protected $_tableName = 'order';

    protected $_fields = array(
        'id'=>'id',
        'hotelId'=>'hotel_id',
        'roomId'=>'room_id',
        'userId'=>'user_id',
        'code'=>'code',
        'comment'=>'comment',
        'range'=>'range',
        'status'=>'status',
        'addTime'=>'add_time'
    );

    protected $_mappers = array(
        'hotel'=>array(
            'Type'=>'hasOne',
            'Fetch'=>FetchType::LAZY,
            'Target'=>'Hotel',
            'Mapping'=>array(
                'hotelId'=>'id'
            ),
        ),
        'room'=>array(
            'Type'=>'hasOne',
            'Fetch'=>FetchType::LAZY,
            'Target'=>'Room',
            'Mapping'=>array(
                'roomId'=>'id'
            ),
        ),
        'user'=>array(
            'Type'=>'hasOne',
            'Fetch'=>FetchType::LAZY,
            'Target'=>'User',
            'Mapping'=>array(
                'UserId'=>'id'
            ),
        ),
        'schedules'=>array(
            'Type'=>'hasMany',
            'Fetch'=>FetchType::LAZY,
            'Target'=>'Schedule',
            'Mapping'=>array(
                'id'=>'orderId'
            )
        )
    );

}