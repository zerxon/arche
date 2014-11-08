<?php
/**
 * 
 * @author: wallace wallaceleung@163.com
 * @date: 14-10-27
 */

import('Model.entity.Order');
import('Model.entity.Schedule');

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

    public function confirmOrder($date, $roomId, $userId, $comment) {
        $status = false;

        $roomId = intval($roomId);
        $userId = intval($userId);

        if(!$roomId || !$userId)
            return $status;

        $strDate = '';
        foreach($date as $d) {
            $strDate .= getFriendlyTime($d, 'Y-m-d');
            $strDate .= "(星期".week(date('w', $d)).")|";
        }

        if($strDate) {
            $strDate = substr($strDate, 0, strlen($strDate) - 1);
            $order = new Order();
            $order->roomId($roomId);
            $order->userId($userId);
            $order->code(microTimestamp());
            $order->comment($comment);
            $order->range($strDate);
            $order->addTime(time());

            $orderId = $order->save();

            //插入数据到schedule
            if($orderId > 0) {
                $status = true;

                foreach($date as $d) {
                    $schedule = new Schedule();
                    $schedule->orderId($orderId);
                    $schedule->roomId($roomId);
                    $schedule->checkinDate($d);

                    $schedule->save();
                }
            }
        }

        return $status;
    }
}