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
    'AutoSignInFilter' => array(
        'Enable' => true,
        'Path' => '*',
        'Type' => FilterType::ALL,
    ),

    'AdminPermissionFilter' => array(
        'Enable' => true,
        'Path' => '^/admin',
        'Type' => FilterType::EXCEPT,
        'Option' => array(
            '^/admin/signIn',
            '^/admin/doSignIn'
        )
    ),

    'AdminSignedInFilter' => array(
        'Enable' => true,
        'Path' => '^/admin',
        'Type' => FilterType::CONTAIN,
        'Option' => array(
            '^/admin/signIn',
            '^/admin/doSignIn'
        )
    ),

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
    ),

    'MerchantEnterFilter' => array(
        'Enable' => true,
        'Path' => '^/account/merchant/step',
        'Type' => FilterType::CONTAIN,
        'Option' => array(
            '^/account/merchant/step$',
            '^/account/merchant/step1',
            '^/account/merchant/step2',
            '^/account/merchant/step3'
        )
    ),

    'MerchantPermissionFilter' => array(
        'Enable' => true,
        'Path' => '^/account/merchant',
        'Type' => FilterType::EXCEPT,
        'Option' => array(
            '^/account/merchant/step',
            '^/account/merchant/doStep',
            '^/account/merchant/roomPhotoUpload',
            '^/account/merchant/uploadLogo',
            '^/account/merchant/roomPhotoDelete'
        )
    ),

);