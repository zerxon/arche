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

    public function __construct() {
        $this->_hotelORM = new ORM('hotel');
    }

    public function getHotelsByPage($pageIndex, $pageSize) {
        $hotelsPage = $this->_hotelORM->selectAll()
            ->fetch('user')
            ->orderBy(array('id'=>'desc'))
            ->queryPage($pageIndex, $pageSize);

        return $hotelsPage;
    }

    public function getOneById($id) {
        $hotel = new Hotel();
        $hotel->findOne($id);

        return $hotel->toArray();
    }

    public function save($hotel) {
        return $hotel->save();
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