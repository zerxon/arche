<?php
/**
 * 
 * @author: wallace wallaceleung@163.com
 * @date: 14-10-19
 */

import('Model.service.HotelService');
import('Model.service.RoomService');
import('Library.Ext.TipsType');

class HotelAPIController extends APIController {

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

    public function all() {
        $this->_initHotelService();

        $pageIndex = intval($_GET['pageIndex']) > 0 ? intval($_GET['pageIndex']) : 1;
        $pageSize = intval($_GET['pageSize']) > 0 ? intval($_GET['pageSize']) : 10;

        $params = array(
            'status'=>1
        );

        $order = array(
            'id'=>ORDER_TYPE::DESC
        );

        $hotelsPage = $this->_hotelService->getHotelsByPage($pageIndex, $pageSize, $params, $order);
        $this->_success = true;
        $this->_data = $hotelsPage;
    }

    public function detail() {
        $this->_initHotelService();

        $hotelId = intval($_GET['id']);

        if($hotelId > 0)
            $hotel = $this->_hotelService->getOneByIdWithRooms($hotelId);

        if($hotel) {
            $this->_success = true;
            $this->_data = $hotel;
        }
        else {
            $this->_error = API_ERROR_TYPE::INVALID_PARAMS;
            $this->_message = '参数无效';
        }
    }

    public function order() {
        $this->_initRoomService();

        $id = intval($_REQUEST['room_id']);

        if(!$id) {
            $this->_error = API_ERROR_TYPE::INVALID_PARAMS;
            $this->_message = '参数无效';
        }
        else {
            $roomService = RoomService::getInstance();
            $room = $roomService->getOneByIdWithHotel($id);

            if(!$room) {
                $this->_error = API_ERROR_TYPE::INVALID_PARAMS;
                $this->_message = '参数无效';
            }
            else {
                $schedules = $roomService->getAvailableSchedule($id, $room['amount']);

                if($schedules) {
                    $this->_success = true;
                    $this->_data = $schedules;
                }
                else {
                    $this->_error = API_ERROR_TYPE::INVALID_PARAMS;
                    $this->_message = '获取时间表失败';
                }
            }

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