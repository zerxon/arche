<?php

/**
 * @author: wallace wallaceleung@163.com
 * @date: 14-10-19
 *
 * 规则说明：
 *  模板
    array(
        '/{moduleRule}' => '{module}'
        '/{moduleRule}' => array(
            'Controller' => '{module}',
            'Pattern' => array(
                '/{actionRule}/...' => '{action}'
                '/{actionRule}/...' => '{action?id=$1}'
            );
        );
    );

 *  例子:
    array(
        '/' => 'index/Index',
        '/store' => 'test/Store',
        '/hotel.html' => array(
            'Controller' => 'hotel/Hotel',
            'Pattern' => array(
            '/' => 'act',
            '/(\d+)/(\w+)' => 'detail?id=$1&type=$2',
            '/(\d+)/(\w+)/(\w+)' => 'detail?abc=$1&type=$2&order=$3',
            '/ect/(\d+)' => 'et'
            )
        ),
    );

 *  1. {moduleRule}的 '/' 对应的是默认控制器，默认控制器不能为数组，并且有且只有一个index action
 *  2. {moduleRule}和{actionRule} 为正则表达式，用于分隔各rule的 "/" 不必转义为 "\/"， 且不用写控制器类后缀字符串：Controller
 *  3. 若{module}不为数组， 则其值为对应的控制器，以Controller目录作为根目录，因此不以 "/" 开头。Router根据url匹配到action名，自动调用控制器类对应相同名称的action方法
 *  4. 若{module}为数组，则必须包含Controller和Pattern2项，Controller为对应的控制器, Pattern为一个或多个action匹配键值
 *  5. Pattern数组只作为处理一些自定义action规则，控制器类自带的公有action方法可省略不写
 *  6. Pattern匹配参数的例子: '/(\d+)/(\w+)' => 'detail?id=$1&type=$2'
 */

return array(
    '/test' =>  'test/Test',
    '/hotel' => array(
        'Controller' => 'hotel/Hotel',
        'Pattern' => array(
            '/detail/(\d+)' => 'detail?id=$1'
        )
    ),
    '/account/merchant'=>'account/Merchant',
    '/account'=>'account/Account',
    '/' => 'index/Index',
    '/admin/order' => 'admin/Order',
    '/admin/room' => 'admin/Room',
    '/admin/user' => 'admin/User',
    '/admin/hotel' => 'admin/Hotel',
    '/admin' => 'admin/Index',
);