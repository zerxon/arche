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
        $this->_display('merchant/my_hotel');
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

        $this->_display('merchant/hotel_room');
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

    public function step() {

        $this->_display('merchant/step');
    }

    public function step1() {
        if($_SESSION['hotel']) {
            $hotel = unserialize($_SESSION['hotel']);

            $hotel->name(base64_decode($hotel->name()));
            $hotel->otherTel(base64_decode($hotel->otherTel()));
            $hotel->address(base64_decode($hotel->address()));
            $hotel->desc(base64_decode($hotel->desc()));

            $this->_assign('hotel', $hotel->toArray());
        }

        $this->_display('merchant/step_1');
    }

    public function doStep1() {
        $userId = intval($_SESSION[SESSION_USER]['id']);

        $name = trim($_POST['name']);
        $tel = trim($_POST['tel']);
        $otherTel = trim($_POST['other_tel']);
        $address = trim($_POST['address']);
        $desc = trim($_POST['desc']);
        $addTime = time();

        $status = true;

        if($userId == 0)
            $status = false;
        else if(!$name)
            $status = false;
        else if(!$tel)
            $status = false;
        else if(!$address)
            $status = false;

        if($status) {
            $hotel = new Hotel();
            $hotel->name(base64_encode($name));
            $hotel->tel($tel);
            $hotel->otherTel(base64_encode($otherTel));
            $hotel->address(base64_encode($address));
            $hotel->desc(base64_encode($desc));
            $hotel->addTime($addTime);
            $hotel->userId($userId);

            //logo
            if($_SESSION['hotel_logo'])
                $hotel->logo($_SESSION['hotel_logo']);

            $_SESSION['hotel'] = serialize($hotel);

            $_SESSION[TIPS_TYPE] = TipsType::SUCCESS;
            $_SESSION[TIPS] = '旅馆信息添加成功，请填写房间信息';

            $this->_redirect(SITE_URL.'account/merchant/step2');
        }
        else {
            $_SESSION[TIPS_TYPE] = TipsType::ERROR;
            $_SESSION[TIPS] = '您还没正确填写旅馆信息';

            $this->_redirect(SITE_URL.'account/merchant/step1');
        }
    }

    public function step2() {

        if(!$_SESSION['hotel']) {
            $_SESSION[TIPS_TYPE] = TipsType::ERROR;
            $_SESSION[TIPS] = '您还没正确填写旅馆信息';

            $this->_redirect(SITE_URL.'account/merchant/step1');
        }

        if(is_array($_SESSION['rooms'])) {
            $rooms = array();
            foreach($_SESSION['rooms'] as $item) {
                $obj = unserialize($item);

                $obj->name(base64_decode($obj->name()));
                $obj->desc(base64_decode( $obj->desc()));

                $room = $obj->toArray();
                $room['timeId'] = $obj->timeId();

                array_push($rooms ,$room);
            }
            $this->_assign('rooms', $rooms);
        }

        $this->_assign('type', 'step2');
        $this->_display('merchant/step_2');
    }

    public function doStep2() {

        $status = true;

        $userId = intval($_SESSION[SESSION_USER]['id']);

        $name = trim($_POST['name']);
        $price = floatval($_POST['price']);
        $otherPrice = intval($_POST['other_price']);
        $amount = intval($_POST['amount']);
        $desc = trim($_POST['desc']);

        $timeId = $_POST['time_id'];

        if($userId == 0)
            $status = false;
        if(!$name || mb_strlen($name, 'utf-8') < 2)
            $status = false;
        else if($amount < 1)
            $status = false;
        else if($price <= 0)
            $status = false;

        if($status) {
            $room = new Room();
            $room->name(base64_encode($name));
            $room->price($price);
            $room->otherPrice($otherPrice);
            $room->desc(base64_encode($desc));
            $room->amount($amount);
            $room->stock($amount);

            if(!$timeId || !isset($_SESSION['rooms'][$timeId])) {
                $timeId = time();
            }

            $room->timeId($timeId);
            $_SESSION['rooms'][$timeId] = serialize($room);
        }

        if($status) {
            $_SESSION[TIPS_TYPE] = TipsType::SUCCESS;
            $_SESSION[TIPS] = '操作成功，您可以继续添加或者选择下一步';
        }
        else {
            $_SESSION[TIPS_TYPE] = TipsType::ERROR;
            $_SESSION[TIPS] = '操作失败，提交的房间资料有误';
        }

        $this->_redirect(SITE_URL.'account/merchant/step2');

    }

    public function step3() {
        if(!$_SESSION['hotel']) {
            $_SESSION[TIPS_TYPE] = TipsType::ERROR;
            $_SESSION[TIPS] = '您还没正确填写旅馆信息';

            $this->_redirect(SITE_URL.'account/merchant/step1');
        }
        else if(!is_array($_SESSION['rooms']) || count($_SESSION['rooms']) < 1 ) {
            $_SESSION[TIPS_TYPE] = TipsType::ERROR;
            $_SESSION[TIPS] = '您还没正确填写房间信息';

            $this->_redirect(SITE_URL.'account/merchant/step2');
        }
        else {
            $this->_initHotelService();
            $this->_initRoomService();

            $hotel = unserialize($_SESSION['hotel']);
            $hotel->name(base64_decode($hotel->name()));
            $hotel->otherTel(base64_decode($hotel->otherTel()));
            $hotel->address(base64_decode($hotel->address()));
            $hotel->desc(base64_decode($hotel->desc()));

            $hotelId = $this->_hotelService->save($hotel);

            if($hotelId > 0) {
                unset($_SESSION['hotel_logo']);

                foreach($_SESSION['rooms'] as $item) {
                    $room = unserialize($item);
                    $room->hotelId($hotelId);
                    $room->name(base64_decode($room->name()));
                    $room->desc(base64_decode( $room->desc()));

                    $status = $this->_roomService->save($room);

                    if(!$status)
                        break;
                }
            }
        }

        if(!$status) {
            $_SESSION[TIPS_TYPE] = TipsType::ERROR;
            $_SESSION[TIPS] = '系统出错，有需要请联系我们';

            $this->_redirect(SITE_URL.'account/merchant/step');
        }

        $this->_display('merchant/step_3');
    }
}