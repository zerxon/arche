<?php
/**
 * 
 * @author: wallace wallaceleung@163.com
 * @date: 14-10-19
 */

import('Model.service.HotelService');
import('Model.service.RoomService');

class HotelController extends Controller {

    private $_hotelService;
    private $_roomService;

    private function _initRoomService() {
        if($this->_roomService == null)
            $this->_roomService = RoomService::getInstance();
    }

    private function _initHotelService() {
        if($this->_hotelService == null)
            $this->_hotelService = HotelService::getInstance();
    }

    public function detail() {
        $this->_initHotelService();

        $hotelId = intval($_GET['id']);

        if($hotelId > 0)
            $hotel = $this->_hotelService->getOneByIdWithRooms($hotelId);

        if(!$hotel)
            error('Invalid hotel id');

        foreach($hotel['rooms'] as $index => $room) {
            if($room['photos']) {
                $photosArray = explode('|', $room['photos']);
                $room['photosArray'] = $photosArray;
                $hotel['rooms'][$index] = $room;
            }
        }

        $this->_assign('hotel', $hotel);
        $this->_display('hotel/detail');
    }

    public function order() {
        $this->_initRoomService();

        $id = intval($_GET['room_id']);

        if(!$id)
            error('参数无效');

        $roomService = RoomService::getInstance();
        $room = $roomService->getOneByIdWithHotel($id);

        if(!$room)
            error('参数无效');

        $schedules = $roomService->getAvailableSchedule($id, $room['amount']);

        if($schedules == false)
            error('无法获取时间表');

        $this->_assign('room', $room);
        $this->_assign('schedules', $schedules);
        $this->_display('hotel/order');
    }

    public function doOrder() {
        $this->_initRoomService();

        $id = intval($_POST['room_id']);
        $date = $_POST['date'];

        if(!$id)
            error('参数无效');

        $roomService = RoomService::getInstance();
        $room = $roomService->getOneByIdWithHotel($id);

        if(!$room)
            error('参数无效');

        $schedules = $roomService->getAvailableSchedule($id, $room['amount']);

        if($schedules == false)
            error('无法获取时间表');


        debug($date);
    }

}