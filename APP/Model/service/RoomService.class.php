<?php
/**
 * 
 * @author: wallace wallaceleung@163.com
 * @date: 14-10-24
 */

import('Model.entity.Room');

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

    public function save($room) {
        return $room->save();
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