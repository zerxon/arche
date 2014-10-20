<?php
/**
 * 
 * @author: wallace wallaceleung@163.com
 * @date: 14-10-3
 */

return array(
    'is_debug'=>1,
    'db'=>array(
        'host'=>'localhost',
        'user'=>'root',
        'pwd'=>'toor',
        'name'=>'takeout',
        'prefix'=>''
    ),
    'controller'=>array(
        'base_class'=>'Library.Core.Controller.Controller',
        'class_suffix'=>'Controller'
    ),
    'api_format'=>'json', /* string | json | xml */
    'tpl'=>'.html',
    'filter_enable'=>true
);