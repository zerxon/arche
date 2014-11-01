<?php
/**
 * 
 * @author: wallace wallaceleung@163.com
 * @date: 14-10-31
 */

import('Model.service.HotelService');
import('Model.service.RoomService');
import('Library.Ext.TipsType');
import('Library.Ext.ImageUpload');

class MerchantController extends Controller {

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

    public function hotelEdit() {
        $this->_initHotelService();

        $userId = intval($_SESSION[SESSION_USER]['id']);

        $params = array(
            'userId'=>$userId
        );

        $hotel = $this->_hotelService->getOneByParams($params)->toArray();
        $this->_assign('hotel', $hotel);
        $this->_display('merchant/hotel_edit');
    }

    public function doHotelEdit() {
        $this->_initHotelService();

        $hotelId = intval($_POST['hotel_id']);
        $name = trim($_POST['name']);
        $tel = trim($_POST['tel']);
        $otherTel = trim($_POST['other_tel']);
        $address = trim($_POST['address']);
        $desc = trim($_POST['desc']);

        $userId = intval($_SESSION[SESSION_USER]['id']);

        $status = true;

        $params = array(
            'id'=>$hotelId,
            'userId'=>$userId
        );

        $hotel = $this->_hotelService->getOneByParams($params);

        if(empty($hotel)) {
            $status = false;
            $_SESSION[TIPS_TYPE] = TipsType::WARNING;
            $_SESSION[TIPS] = '您无权限进行该操作';
        }

        if($status) {
            $hotel->name($name);
            $hotel->tel($tel);
            $hotel->otherTel($otherTel);
            $hotel->address($address);
            $hotel->desc($desc);

            if($_SESSION['hotel_logo'])
                $hotel->logo(oneTimeSession('hotel_logo'));

            $status = $this->_hotelService->doEdit($hotel);

            if(!$status) {
                $_SESSION[TIPS_TYPE] = TipsType::ERROR;
                $_SESSION[TIPS] = '修改失败';
            }
            else {
                $_SESSION[TIPS_TYPE] = TipsType::SUCCESS;
                $_SESSION[TIPS] = '修改成功';
            }
        }

        $this->_redirect(SITE_URL.'account/merchant/hotelEdit');
    }

    public function uploadLogo() {
        $data = ImageUpload::receive($_FILES['upload'], 'Public/upload/hotel/', ROOT_PATH);

        if($data['status'])
            $_SESSION['hotel_logo'] = $data['data']['path'];

        $this->_output($data, 'json');
    }

    public function doRoomAdd() {
        $this->_initRoomService();

        $userId = $_SESSION[SESSION_USER]['id'];

        $name = $_POST['name'];
        $price = $_POST['price'];
        $otherPrice = $_POST['other_price'];
        $amount = $_POST['amount'];
        $desc = $_POST['desc'];

        $status =  $this->_roomService->doAdd($name, $price, $otherPrice, $amount, $desc, $userId);

        if(!$status) {
            $_SESSION[TIPS_TYPE] = TipsType::ERROR;
            $_SESSION[TIPS] = '添加失败';
        }
        else {
            $_SESSION[TIPS_TYPE] = TipsType::SUCCESS;
            $_SESSION[TIPS] = '添加成功';
        }

        $this->_redirect(SITE_URL.'account/merchant/roomEdit');
    }

    public function roomEdit() {
        $this->_initHotelService();

        $userId = $_SESSION[SESSION_USER]['id'];
        $hotel = $this->_hotelService->getOneByUserId($userId);

        if($hotel && $hotel['rooms']) {
            $rooms = array_reverse($hotel['rooms']);
            $this->_assign('rooms', $rooms);
        }

        $this->_display('merchant/room_edit');
    }

    public function doRoomEdit() {
        $this->_initRoomService();

        $userId = $_SESSION[SESSION_USER]['id'];

        $roomId = $_POST['room_id'];
        $name = $_POST['name'];
        $price = $_POST['price'];
        $otherPrice = $_POST['other_price'];
        $amount = $_POST['amount'];
        $desc = $_POST['desc'];

        $status =  $this->_roomService->doEdit($roomId ,$name, $price, $otherPrice, $amount, $desc, $userId);

        if($status === -1) {
            $_SESSION[TIPS_TYPE] = TipsType::ERROR;
            $_SESSION[TIPS] = '由于房间总量的修改，导致改房间剩余量小于1，因此修改失败';
        }
        else if(!$status) {
            $_SESSION[TIPS_TYPE] = TipsType::ERROR;
            $_SESSION[TIPS] = '修改失败';
        }
        else {
            $_SESSION[TIPS_TYPE] = TipsType::SUCCESS;
            $_SESSION[TIPS] = '修改成功';
        }

        $this->_redirect(SITE_URL.'account/merchant/roomEdit');
    }
}