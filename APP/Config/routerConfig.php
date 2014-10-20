<?php
/**
 * 
 * @author: wallace wallaceleung@163.com
 * @date: 14-10-19
 */

/**
 * 规则说明：
 *   1. '/' 对应的是默认控制器，默认控制器只有一个index方法
 */
return array(
    '/' => 'index/Index',
    '/store' => 'test/Store',
    '/hotel' => array(
        'Controller' => 'hotel/Hotel',
        'Pattern' => array(
            '/' => 'act',
            '/(\d+)/(\w+)' => 'detail?id=$1&type=$2',
            '/(\d+)/(\w+)/(\w+)' => 'detail?abc=$1&type=$2&order=$3',
            '/ect/(\d+)' => 'et'
        )
    ),
);