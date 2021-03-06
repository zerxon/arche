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

        $orderType = array(
            'id'=>ORDER_TYPE::DESC
        );

        $ordersPage = $this->_orderService->getOrdersByPage(1, 10, null, $orderType);

        $this->_assign('page', $ordersPage);
        $this->_display('admin/order_all');
    }

}