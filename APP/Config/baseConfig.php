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
        'base_class'=>'Library.Core.Controller.Controller',
        'class_suffix'=>'Controller'
    ),
    'view'=>array(
        'cache_enable'=>true,
        'cache_expiry'=>86400,
        'cache_folder'=>'template',
        'suffix'=>'.html'
    ),
    'api_format'=>'json', /* string | json | xml */
    'filter_enable'=>true
);