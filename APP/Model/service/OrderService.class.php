<?php
/**
 * 
 * @author: wallace wallaceleung@163.com
 * @date: 14-10-27
 */

import('Model.Entity.Order');

class OrderService {
    private $_orderORM;

    private static $_instance;

    public static function getInstance() {
        if(self::$_instance == null) {
            self::$_instance = new self;
        }

        return self::$_instance;
    }

    private function __construct() {
        $this->_orderORM = new ORM('Order');
    }

    public function getOrdersByPage($pageIndex, $pageSize, $params = null, $order = null) {

        $orm = $this->_orderORM->selectAll()->fetch('room', 'room.hotel', 'user');

        if(is_array($params) && count($params) > 0)
            $orm = $orm->where($params);

        $ordersPage = $orm->orderBy($order)
            ->queryPage($pageIndex, $pageSize);

        return $ordersPage;
    }
}