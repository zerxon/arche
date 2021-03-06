<?php
/**
 * 
 * @author: wallace wallaceleung@163.com
 * @date: 14-10-27
 */

import('Model.entity.Order');
import('Model.service.ScheduleService');
import('Library.Ext.CodeCreator');

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

    public function getHotelOrdersByPage($merchantId, $pageIndex, $pageSize, $params = null, $order = null) {
        $merchantId = intval($merchantId);

        if($merchantId > 0) {
            $hotel = new Hotel();
            $hotel->findOne(array('userId'=>$merchantId));

            if(!$hotel->isEmpty()) {
                $orm = $this->_orderORM->selectAll()->fetch('room', 'user');

                $params['hotelId'] = $hotel->id();

                if(is_array($params) && count($params) > 0)
                    $orm = $orm->where($params);

                $ordersPage = $orm->orderBy($order)
                    ->queryPage($pageIndex, $pageSize);
            }
        }

        return $ordersPage;
    }

    public function getOne($orderId) {
        $order = new Order();
        $order->findOne($orderId);

        if(!$order->isEmpty())
            return $order->toArray();
        else
            return null;
    }

    public function getOneByCode($code) {
        $code = trim($code);

        if($code) {
            $order = $this->_orderORM->selectAll()
                        ->fetch('room', 'room.hotel', 'user')
                        ->where()->field('code')->eq($code)
                        ->queryOne();

            return $order;
        }

        return null;
    }

    public function confirmOrder($date, $roomId, $userId, $comment) {
        $status = false;

        $roomId = intval($roomId);
        $userId = intval($userId);

        if(!$roomId || !$userId)
            return $status;

        $room = new Room();
        $room->findOne($roomId);

        if($room->isEmpty())
            return $status;

        $strDate = '';
        foreach($date as $d) {
            $strDate .= getFriendlyTime($d, 'Y-m-d');
            $strDate .= "(星期".week(date('w', $d)).")|";
        }

        if($strDate) {
            $strDate = substr($strDate, 0, strlen($strDate) - 1);
            $order = new Order();
            $order->hotelId($room->hotelId());
            $order->roomId($roomId);
            $order->userId($userId);
            //$order->code(microTimestamp());
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

    public function merchantConfirmOrder($orderId, $hotelId) {
        $orderId = intval($orderId);
        $hotelId = intval($hotelId);

        if($orderId < 1 || $hotelId < 1)
            return false;

        $code = CodeCreator::produce();

        $rows = $this->_orderORM->update()
                    ->field('status')->eq(1)
                    ->nextField('code')->eq($code)
                    ->where()
                    ->field('id')->eq($orderId)
                    ->andField('hotelId')->eq($hotelId)
                    ->execute();

        $status = $rows > 0;

        return $status;
    }

    public function cancelOrderByIdAndHotelId($orderId, $hotelId) {
        $orderId = intval($orderId);
        $hotelId = intval($hotelId);

        if($orderId < 1 || $hotelId < 1)
            return false;

        $rows = $this->_orderORM->update()
            ->field('status')->eq(2)
            ->where(array('id'=>$orderId, 'hotelId'=>$hotelId))
            ->queryAffectedRows();

        $status = $rows > 0;

        //删除对应的schedule
        if($status) {
            $scheduleService = ScheduleService::getInstance();
            $status = $scheduleService->deleteSchedulesByOrderId($orderId);
        }

        return $status;
    }

    public function ignoreOrderByIdAndHotelId($orderId, $hotelId) {
        $orderId = intval($orderId);
        $hotelId = intval($hotelId);

        if($orderId < 1 || $hotelId < 1)
            return false;

        $rows = $this->_orderORM->update()
            ->Field('isMerchantIgnore')->eq(1)
            ->where(array('id'=>$orderId, 'hotelId'=>$hotelId))
            ->queryAffectedRows();

        $status = $rows > 0;

        return $status;
    }

    public function cancelOrderByIdAndUserId($orderId, $userId) {
        $orderId = intval($orderId);
        $userId = intval($userId);

        if($orderId < 1 || $userId < 1)
            return false;

        $rows = $this->_orderORM->update()
            ->field('status')->eq(2)
            ->where(array('id'=>$orderId, 'userId'=>$userId))
            ->queryAffectedRows();

        $status = $rows > 0;

        //删除对应的schedule
        if($status) {
            $scheduleService = ScheduleService::getInstance();
            $status = $scheduleService->deleteSchedulesByOrderId($orderId);
        }

        return $status;
    }

    public function ignoreOrderByIdAndUserId($orderId, $userId) {
        $orderId = intval($orderId);
        $userId = intval($userId);

        if($orderId < 1 || $userId < 1)
            return false;

        $rows = $this->_orderORM->update()
            ->field('isUserIgnore')->eq(1)
            ->where(array('id'=>$orderId, 'userId'=>$userId))
            ->queryAffectedRows();

        $status = $rows > 0;

        //删除对应的schedule
        if($status) {
            $scheduleService = ScheduleService::getInstance();
            $status = $scheduleService->deleteSchedulesByOrderId($orderId);
        }

        return $status;
    }

    public function codeConfirm($code, $hotelId) {
        $code = trim($code);
        $hotelId = intval($hotelId);

        if(strlen($code) == 0 || $hotelId < 0)
            return false;

        $row = $this->_orderORM->update()
                    ->field('status')->eq(3)
                    ->where()
                    ->field('code')->eq($code)
                    ->andField('hotelId')->eq($hotelId)
                    ->andField('status')->eq(1)
                    ->queryAffectedRows();

        return $row > 0;
    }

}