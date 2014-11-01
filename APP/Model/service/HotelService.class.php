<?php
/**
 * 
 * @author: wallace wallaceleung@163.com
 * @date: 14-10-21
 */

import('Model.entity.Hotel');

class HotelService {
    private $_hotelORM;

    private static $_instance;

    public static function getInstance() {
        if(self::$_instance == null) {
            self::$_instance = new self;
        }

        return self::$_instance;
    }

    private function __construct() {
        $this->_hotelORM = new ORM('Hotel');
    }

    public function getHotelsByPage($pageIndex, $pageSize) {
        $hotelsPage = $this->_hotelORM->selectAll()
            ->fetch('user')
            ->orderBy(array('id'=>ORDER_TYPE::DESC))
            ->queryPage($pageIndex, $pageSize);

        return $hotelsPage;
    }

    public function getOneByParams($params) {
        $hotel = new Hotel();
        $hotel->findOne($params);

        return $hotel;
    }

    public function getOneByUserId($userId) {
        $userId = intval($userId);
        $hotel = array();
        if($userId)
            $hotel = $this->_hotelORM->selectAll()
                ->fetch('rooms')
                ->where()->field('userId')->eq($userId)
                ->queryOne();


        return $hotel;
    }

    public function getOneById($id) {
        $hotel = new Hotel();
        $hotel->findOne($id);

        return $hotel->toArray();
    }

    public function save($hotel) {
        return $hotel->save();
    }

    public function doEdit($hotel) {
        $name = trim($hotel->name());
        $tel = trim($hotel->tel());
        $address = trim($hotel->address());

        $status = true;

        if(!$name)
            $status = false;
        else if(!$tel)
            $status = false;
        else if(!$address)
            $status = false;

        if($status) {
            $hotel->save();
        }

        return $status;
    }

    public function delete($id) {

        if(intval($id) < 1)
            return false;

        $status = $this->_hotelORM->delete()
            ->where()
            ->field('id')
            ->eq($id)
            ->execute();

        return $status;
    }
}