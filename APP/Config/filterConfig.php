<?php
/**
 * 
 * @author: wallace wallaceleung@163.com
 * @date: 14-5-18
 *
     return array(
        'AdminPermissionFilter' => array(
        'Enable' => true,
        'Path' => '^/admin',            //正则表达式，区分大小写
        'Type' => FilterType::EXCEPT,   //FilterType::ALL | FilterType::CONTAIN | FilterType::EXCEPT
        'Option' => '^/admin/signin'  //正则表达式，区分大小写，可为数组
        )
     );
 */

return array(
    'AdminPermissionFilter' => array(
        'Enable' => false,
        'Path' => '^/admin',
        'Type' => FilterType::EXCEPT,
        'Option' => '^/admin/signIn'
    )
);