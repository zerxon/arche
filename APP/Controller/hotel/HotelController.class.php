<?php
/**
 * 
 * @author: wallace wallaceleung@163.com
 * @date: 14-10-19
 */

import('Model.service.HotelService');
import('Model.service.RoomService');
import('Model.service.OrderService');
import('Library.Ext.TipsType');

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

        $id = intval($_REQUEST['room_id']);

        if(!$id)
            $error = '参数无效';

        $roomService = RoomService::getInstance();
        $room = $roomService->getOneByIdWithHotel($id);

        if(!$room)
            $error = '参数无效';

        $schedules = $roomService->getAvailableSchedule($id, $room['amount']);

        if($schedules == false)
            $error = '无法获取时间表';

        if($error) {
            $_SESSION[TIPS_TYPE] = TipsType::ERROR;
            $_SESSION[TIPS] = $error;
            $this->_redirect(SITE_URL);
        }
        else {
            $this->_assign('room', $room);
            $this->_assign('schedules', $schedules);
            $this->_display('hotel/order');
        }

    }

    public function doOrder() {
        $this->_initRoomService();

        $roomId = intval($_POST['room_id']);
        $date = $_POST['date'];
        $comment = trim($_POST['comment']);

        $userId = intval($_SESSION[SESSION_USER]['id']);

        if(!$roomId || !$userId) {
            $error = '参数无效';
        }

        if(empty($date)) {
            $error = '请选择入住时间';
            $_SESSION[TIPS_TYPE] = TipsType::WARNING;
            $_SESSION[TIPS] = $error;
            $this->_redirect(SITE_URL.'hotel/order?room_id='.$roomId);
        }

        $roomService = RoomService::getInstance();
        $room = $roomService->getOneByIdWithHotel($roomId);

        if(!$room)
            $error = '参数无效';

        $schedules = $roomService->getAvailableSchedule($roomId, $room['amount']);

        if($schedules == false)
            $error = '无法获取时间表';

        //检查是否有不合法的预订时间
        if(!$error) {
            foreach($date as $d) {
                if(!isset($schedules[$d])) {
                    $error = "会话过期， 请重新下单";
                    $_SESSION[TIPS_TYPE] = TipsType::WARNING;
                    $_SESSION[TIPS] = $error;
                    $this->_redirect(SITE_URL.'hotel/order?room_id='.$roomId);
                }
                else if($schedules[$d] == 0) {
                    $error = '您的订单中含有预订已满的房间，请重新下单';
                    $_SESSION[TIPS_TYPE] = TipsType::WARNING;
                    $_SESSION[TIPS] = $error;
                    $this->_redirect(SITE_URL.'hotel/order?room_id='.$roomId);
                }
            }
        }

        if(!$error) {
            $orderService = OrderService::getInstance();
            $status = $orderService->confirmOrder($date, $roomId, $userId, $comment);
            if(!$status)
                $error = '下单失败';
        }

        if($error) {
            $_SESSION[TIPS_TYPE] = TipsType::ERROR;
            $_SESSION[TIPS] = $error;
            $this->_redirect(SITE_URL);
        }
        else {
            $_SESSION[TIPS_TYPE] = TipsType::ERROR;
            $_SESSION[TIPS] = $error;
            $this->_redirect(SITE_URL.'account/myOrder');
        }
    }

}