<?php
/**
 * 
 * @author: wallace wallaceleung@163.com
 * @date: 14-4-27
 */

import('Library.Core.Model.Model');
import('Model.entity.Menu');

class Blank extends Model {

    protected $_tableName = 'blank';

    protected $_fields = array(
        'id'=>'id',
        'name'=>'name',
        'sort'=>'sort',
        'parentId'=>'parent_id',
        'storeId'=>'store_id'
    );

    protected $_mappers = array(
        'blanks'=>array(
            'type'=>'hasMany',
            'fetch'=>FetchType::LAZY,
            'target'=>'Blank',
            'mapping'=>array(
                'id'=>'parentId'
            )
        ),
        'menus'=>array(
            'type'=>'hasMany',
            'fetch'=>FetchType::LAZY,
            'target'=>'Menu',
            'mapping'=>array(
                'id'=>'blankId'
            )
        ),
    );

}