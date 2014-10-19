<?php
/**
 * 
 * @author: wallace wallaceleung@163.com
 * @date: 14-8-24
 */

return array(
    '/'=>'index/index/newAction',
    'store/(\d+)#/(\w+)##/order\-(\w+)#'=>'store/store/arFindOne/id/$1/type/$2',
    'abc'=>'store',
);