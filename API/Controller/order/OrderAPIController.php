<?php
/**
 * 
 * @author: wallace wallaceleung@163.com
 * @date: 14-11-26
 */

import('Model.service.OrderService');

class OrderAPIController extends APIController {
    private $_orderService;

    public function __construct() {
        $this->_orderService = OrderService::getInstance();
    }

    public function myOrder() {
        $userId = intval($_SESSION[SESSION_USER]['id']);

        $params = array(
            'userId' => $userId,
            'isUserIgnore'=>0
        );

        $order = array(
            'id' => 'desc'
        );

        $page = $this->_orderService->getOrdersByPage(1, 10, $params, $order);

        if($page['records']) {
            foreach($page['records'] as $index => $record) {
                $strDate = substr($record['range'], 0, strpos($record['range'], '('));
                $date = strtotime($strDate);
                $date += 86400;
                if($date < time())
                    $page['records'][$index]['isExpiry'] =  true;
                else
                    $page['records'][$index]['isExpiry'] = false;
            }
        }

        if($page) {
            $this->_success = true;
            $this->_data = $page;
        }
        else {
            $this->_error = API_ERROR_TYPE::INVALID_PARAMS;
            $this->_message = '获取订单列表失败';
        }
    }

    public function cancelOrder() {
        $orderId = intval($_GET['order_id']);
        $userId = intval($_SESSION[SESSION_USER]['id']);

        $status = true;
        if($orderId < 1)
            $status = false;

        if($status) {
            $status = $this->_orderService->cancelOrderByIdAndUserId($orderId, $userId);
        }

        if($status) {
            $this->_success = true;
        }
        else {
            $this->_error = API_ERROR_TYPE::INVALID_PARAMS;
            $this->_message = '取消订单失败';
        }
    }

    public function ignoreOrder() {
        $orderId = intval($_GET['order_id']);
        $userId = intval($_SESSION[SESSION_USER]['id']);

        $status = true;
        if($orderId < 1)
            $status = false;

        if($status) {
            $status = $this->_orderService->ignoreOrderByIdAndUserId($orderId, $userId);
        }

        if($status) {
            $this->_success = true;
        }
        else {
            $this->_error = API_ERROR_TYPE::INVALID_PARAMS;
            $this->_message = '删除订单失败';
        }
    }
}