<?php
/**
 *
 * @author: wallace wallaceleung@163.com
 * @date: 14-5-18
 *
 * return array(
 * 'AdminPermissionFilter' => array(
 * 'Enable' => true,
 * 'Filter' => 'FilterClass',       //可选，指定配置调用哪个filter class
 * 'Path' => '^/admin',            //正则表达式，区分大小写
 * 'Type' => FilterType::EXCEPT,   //FilterType::ALL | FilterType::CONTAIN | FilterType::EXCEPT
 * 'Option' => '^/admin/signin'  //正则表达式，区分大小写，可为数组
 * )
 * );
 */

return array(
    'UserPermissionFilter' => array(
        'Enable' => true,
        'Path' => '^/account',
        'Type' => FilterType::EXCEPT,
        'Option' => array(
            '^/account/doSignIn',
            '^/account/doSignUp',
            '^/account/checkExist'
        )
    ),

    'OrderPermissionFilter' => array(
        'Enable' => true,
        'Filter' => 'UserPermissionFilter',
        'Path' => '^/hotel',
        'Type' => FilterType::CONTAIN,
        'Option' => array(
            '^/hotel/order',
            '^/hotel/doOrder'
        )
    ),

    'UserSignedInFilter' => array(
        'Enable' => true,
        'Path' => '^/account',
        'Type' => FilterType::CONTAIN,
        'Option' => array(
            '^/account/doSignIn',
            '^/account/doSignUp',
            '^/account/checkExist'
        )
    )

);