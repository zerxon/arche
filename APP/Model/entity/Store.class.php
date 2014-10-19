<?php
/**
 * 
 * @author: wallace wallaceleung@163.com
 * @date: 14-4-27
 */

import('Library.Core.Model.ARModel');
import('Model.entity.Blank');

class Store extends ARModel {

    protected $_tableName = 'store';

    protected  $_fields = array(
        'id'=>'id',
        'name'=>'name',
        'address'=>'address',
        'contact'=>'phone',
        'description'=>'description',
        'isShow'=>'is_show'
    );

    protected $_mappers = array(
        'blanks'=>array(
            'type'=>'hasMany',
            'fetch'=>FetchType::LAZY,
            'target'=>'Blank',
            'mapping'=>array(
                'id'=>'storeId'
            )
        ),
    );

}