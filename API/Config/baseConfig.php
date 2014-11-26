<?php
/**
 * 
 * @author: wallace wallaceleung@163.com
 * @date: 14-10-3
 */

return array(
    'is_debug'=>true,
    'db'=>array(
        'host'=>'localhost',
        'user'=>'root',
        'pwd'=>'toor',
        'name'=>'pa',
        'prefix'=>'pa_'
    ),
    'controller'=>array(
        'base_class'=>'Library.Ext.APIController',
        'class_suffix'=>'APIController'
    ),
    'api_format'=>'json', /* string | json | xml */
    'filter_enable'=>true,
    'time_zone'=>'PRC'
);
