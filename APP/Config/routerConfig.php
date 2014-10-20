<?php
/**
 * 
 * @author: wallace wallaceleung@163.com
 * @date: 14-10-19
 */

return array(
    '/' => 'Index',
    '/store' => 'test/Store',
    '/hotel' => array(
        'Controller' => 'hotel/Hotel',
        'Pattern' => array(
            '/(\d+)/(\w+)' => 'detail?id=$1&type=$2',
            '/(\d+)/(\w+)/(\w+)' => 'detail?abc=$1&type=$2&order=$3',
            '/ect/(\d+)' => 'et'
        )
    ),
);