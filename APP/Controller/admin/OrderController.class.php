<?php
/**
 * 
 * @author: wallace wallaceleung@163.com
 * @date: 14-10-27
 */

import('Model.Service.OrderService');

class OrderController extends Controller {
    private $_orderService ;

    public function __construct() {
        $this->_orderService = OrderService::getInstance();
    }

    public function all() {
        $ordersPage = $this->_orderService->getOrdersByPage(1, 10);

        debug($ordersPage);
    }

}