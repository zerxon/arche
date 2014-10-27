<?php
/**
 * 
 * @author: wallace wallaceleung@163.com
 * @date: 14-10-24
 */

import('Library.Core.Model.ARModel');
import('Model.entity.Hotel');

class Room extends ARModel {

    protected $_tableName = 'room';

    protected $_fields = array(
        'id' => 'id',
        'hotelId' => 'hotel_id',
        'name' => 'name',
        'price' => 'price',
        'amount' => 'amount',
        'stock' => 'stock',
        'desc' => 'desc'
    );

    protected $_mappers = array(
        'hotel' => array(
            'Type' => 'hasOne',
            'Fetch' => FetchType::LAZY,
            'Target' => 'Hotel',
            'Mapping' => array(
                'hotelId' => 'id'
            )
        )
    );
}