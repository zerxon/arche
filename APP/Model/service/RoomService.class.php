<?php
/**
 * 
 * @author: wallace wallaceleung@163.com
 * @date: 14-10-24
 */

import('Model.entity.Room');
import('Model.entity.Hotel');
import('Model.service.Schedule');

class RoomService {
    private $_roomORM;

    private static $_instance;

    public static function getInstance() {
        if(self::$_instance == null) {
            self::$_instance = new self;
        }

        return self::$_instance;
    }

    private function __construct() {
        $this->_roomORM = new ORM('Room');
    }

    public function getRoomsByPage($pageIndex, $pageSize, $params = null, $order = null) {

        $orm = $this->_roomORM->selectAll()->fetch('hotel');

        if(is_array($params) && count($params) > 0)
            $orm = $orm->where($params);

        $roomsPage = $orm->orderBy($order)
            ->queryPage($pageIndex, $pageSize);

        return $roomsPage;
    }

    public function getOneById($id) {
        $room = new Room();
        $room->findOne($id);

        return $room->toArray();
    }

    public function getOneByIdWithHotel($id) {
        $id = intval($id);

        if($id > 0) {
            $room = $this->_roomORM->selectAll()
                ->fetch('hotel')
                ->where(array('id'=>$id))
                ->queryOne();
        }

        return $room;
    }

    //获取
    public function getAvailableSchedule($roomId, $amount) {
        $roomId = intval($roomId);
        $amount = intval($amount);

        if($roomId < 1 || $amount < 1)
            return false;

        $beginTime = strtotime(date('Y-m-d', time()));
        $endTime = strtotime(date('Y-m-d', strtotime('+1 week')));

        $scheduleOrm = new ORM('Schedule');

        $sql = "select *, count(*) as total from pa_schedule "
            ."where room_id=$roomId and checkin_date between $beginTime and $endTime "
            ."group by checkin_date";
        $records = $scheduleOrm->sql($sql)->queryAll(false, true);

        $schedules = array();
        for($i = 0; $i < 7; $i++) {
            $date = strtotime(date('Y-m-d',strtotime("+$i day")));

            $exist = false;
            foreach($records as $record) {
                if($record['checkin_date'] == $date) {
                    $exist = true;
                    $count =  intval($record['total']);
                    $schedules[$date] = $amount - $count;
                    break;
                }
            }

            if(!$exist)
                $schedules[$date] = $amount;

        }

        return $schedules;
    }

    public function doAdd($name, $price, $otherPrice, $amount, $desc, $userId, $photos = null) {
        $status = true;

        $name = trim($name);
        $price = floatval($price);
        $otherPrice = floatval($otherPrice);
        $amount = intval($amount);
        $desc = trim($desc);

        $userId = intval($userId);

        if($userId == 0)
            $status = false;
        if(!$name || mb_strlen($name, 'utf-8') < 2)
            $status = false;
        else if($amount < 1)
            $status = false;
        else if($price <= 0)
            $status = false;

        if($status) {
            $hotel = new Hotel();
            $hotel->findOne(array(
                'userId' => $userId
            ));

            if(!$hotel->isEmpty()) {
                $hotelId = $hotel->id();
            }
            else {
                $status = false;
            }
        }

        if($status) {
            $room = new Room();
            $room->name($name);
            $room->hotelId($hotelId);
            $room->price($price);
            $room->otherPrice($otherPrice);
            $room->desc($desc);
            $room->amount($amount);
            $room->stock($amount);

            if($photos)
                $room->photos($photos);
            else
                $room->photos('');

            $status = $room->save() > 0;
        }

        return $status;
    }

    public function doEdit($roomId ,$name, $price, $otherPrice, $amount, $desc, $userId, $photos = null) {
        $status = true;

        $roomId = intval($roomId);
        $name = trim($name);
        $price = floatval($price);
        $otherPrice = floatval($otherPrice);
        $amount = intval($amount);
        $desc = trim($desc);

        $userId = intval($userId);

        if($roomId == 0 || $userId == 0)
            $status = false;
        else if(!$name || mb_strlen($name, 'utf-8') < 2)
            $status = false;
        else if($amount < 1)
            $status = false;
        else if($price <= 0)
            $status = false;

        if($status) {
            $room = $this->_roomORM->selectAll()
                ->fetch('hotel')
                ->where()->field('id')->eq($roomId)
                ->queryOne(true);

            if($room && $room->hotel()->userId() == $userId) {
                $room->name($name);
                $room->price($price);
                $room->otherPrice($otherPrice);
                $room->desc($desc);

                if($photos)
                    $room->photos($photos);
                else
                    $room->photos('');

                if($room->amount() != $amount) {
                    $count = $amount- $room->amount();
                    $stock = $room->stock() + $count;

                    if($stock >= 0) {
                        $room->amount($amount);
                        $room->stock($stock);

                    }
                    else
                        return -1;
                }

                $status = $room->save();
            }
            else {
                $status = false;
            }
        }

        return $status;
    }

    public function save($room) {
        return $room->save();
    }

    public function deleteByIdAndUserId($id, $userId) {
        $status = false;

        if(intval($id) > 0 && intval($userId) > 0 ) {
            $room = $this->_roomORM->selectAll()
                ->fetch('hotel')
                ->where()->field('id')->eq($id)
                ->queryOne(true);

            if($room && $room->hotel() && $room->hotel()->userId() == $userId) {
                $status = $room->delete();
            }
        }

        return $status;
    }

    public function delete($id) {

        if(intval($id) < 1)
            return false;

        $status = $this->_roomORM->delete()
            ->where()
            ->field('id')
            ->eq($id)
            ->execute();

        return $status;
    }
}