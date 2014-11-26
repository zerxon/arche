<?php
/**
 * 
 * @author: wallace wallaceleung@163.com
 * @date: 14-10-31
 */

import('Model.service.HotelService');
import('Model.service.RoomService');
import('Model.service.OrderService');
import('Model.service.ScheduleService');
import('Library.Ext.TipsType');
import('Library.Ext.ImageUpload');
import('Library.Ext.Pagination');

class MerchantController extends Controller {

    private $_hotelService;
    private $_roomService;
    private $_orderService;
    private $_scheduleService;

    private function _initRoomService() {
        if($this->_roomService == null)
            $this->_roomService = RoomService::getInstance();
    }

    private function _initHotelService() {
        if($this->_hotelService == null)
            $this->_hotelService = HotelService::getInstance();
    }

    private function _initOrderService() {
        if($this->_orderService == null)
            $this->_orderService = OrderService::getInstance();
    }

    private function _initScheduleService() {
        if($this->_scheduleService == null)
            $this->_scheduleService = ScheduleService::getInstance();
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

    public function roomPhotoUpload() {
        $id = intval($_GET['id']);
        $data = ImageUpload::receive($_FILES['upload'], 'Public/upload/room/', ROOT_PATH);

        if($data['status']) {
            array_push($_SESSION['room_photos'][$id], $data['data']['path']);
        }

        $this->_output($data, 'json');
    }

    public function roomPhotoDelete() {
        $imgUrl = $_GET['imgUrl'];
        $imgUrl = str_replace(BASE_URL, '', $imgUrl);

        $status = unlink($imgUrl);

        $data = array(
            'status' => $status
        );

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

        if(is_array($_POST['photos']) && count($_POST['photos']))
            $photos = implode('|', $_POST['photos']);

        $status =  $this->_roomService->doAdd($name, $price, $otherPrice, $amount, $desc, $userId, $photos);

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

            foreach($rooms as $index => $room) {
                if($room['photos'])
                    $room['photosArray'] = explode('|', $room['photos']);

                $rooms[$index] = $room;
            }

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

        if(is_array($_POST['photos']) && count($_POST['photos']))
            $photos = implode('|', $_POST['photos']);

        $status =  $this->_roomService->doEdit($roomId ,$name, $price, $otherPrice, $amount, $desc, $userId, $photos);

        /*
        if($status === -1) {
            $_SESSION[TIPS_TYPE] = TipsType::ERROR;
            $_SESSION[TIPS] = '由于房间总量的修改，导致改房间剩余量小于1，因此修改失败';
        }
        */

        if(!$status) {
            $_SESSION[TIPS_TYPE] = TipsType::ERROR;
            $_SESSION[TIPS] = '修改失败';
        }
        else {
            $_SESSION[TIPS_TYPE] = TipsType::SUCCESS;
            $_SESSION[TIPS] = '修改成功';
        }

        $this->_redirect(SITE_URL.'account/merchant/roomEdit');
    }

    public function doRoomDelete() {
        $this->_initRoomService();

        $userId = $_SESSION[SESSION_USER]['id'];
        $id = intval($_GET['id']);
        $timeId = intval($_GET['time_id']);

        if($id > 0) {
            $status = $this->_roomService->deleteByIdAndUserId($id, $userId);
        }
        else if($_SESSION['rooms'][$timeId]) {
            $status = true;
            unset($_SESSION['rooms'][$timeId]);
        }

        if($status) {
            $_SESSION[TIPS_TYPE] = TipsType::SUCCESS;
            $_SESSION[TIPS] = '删除成功';
        }
        else {
            $_SESSION[TIPS_TYPE] = TipsType::ERROR;
            $_SESSION[TIPS] = '删除失败';
        }

        if($id > 0) {
            $this->_redirect(SITE_URL.'account/merchant/roomEdit');
        }
        else if($timeId > 0) {
            $this->_redirect(SITE_URL.'account/merchant/step2');
        }
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

                if($room['photos'])
                    $room['photosArray'] = explode('|', $room['photos']);

                array_push($rooms ,$room);
            }

            $rooms = array_reverse($rooms);
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

        $photosArray = $_POST['photos'];

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

            if(is_array($photosArray) && count($photosArray) > 0) {
                $room->photos(implode('|', $photosArray));
            }

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

    public function roomBookCountOperator() {
        $type = $_POST['type'];
        $date = intval($_POST['date']);
        $roomId = intval($_POST['roomId']);

        $status = true;
        $stock = 0;
        $msg = "更新失败";
        if($type != 'sub' && $type !='plus')
            $status = false;

        if($date < 1 || $roomId < 1)
            $status = false;

        if($status) {
            $this->_initRoomService();
            $room = $this->_roomService->getOneByIdWithHotel($roomId);

            if($room['hotel']['userId'] !== $_SESSION[SESSION_USER]['id']) {
                $status = false;
                $msg = '您无权限执行该操作';
            }
            else {
                $this->_initScheduleService();
                $count = $this->_scheduleService->getOneBookSchedule($roomId, $date);

                if($count['offline'] == 0 && $type == 'sub') {
                    $status = false;
                    $msg = '数量为0，不能再减了';
                }
                else if($count['online'] + $count['offline'] == $room['amount'] && $type == 'plus') {
                    $status = false;
                    $msg = '预订(入住)量已达上限，不能执行该操作';
                }

                if($status) {
                    if($type == 'sub')
                        $status = $this->_scheduleService->subOneOffline($roomId, $date);
                    else
                        $status = $this->_scheduleService->addOneOffline($roomId, $date);

                    if($status) {
                        $count['offline'] = $type == 'sub' ? $count['offline'] - 1 : $count['offline'] + 1;
                        $stock = $room['amount'] - $count['online'] - $count['offline'];
                        $msg = "更新成功";
                    }
                }
            }
        }
        else {
            $msg = '参数错误';
        }

        $data = array(
            'status'=>$status,
            'offlineBook' => $count['offline'],
            'stock' =>  $stock,
            'message' => $msg
        );

        $this->_output($data, 'json');
    }

    public function hotelCenter() {
        $this->_initHotelService();
        $this->_initRoomService();
        $this->_initScheduleService();

        $userId = $_SESSION[SESSION_USER]['id'];
        $hotel = $this->_hotelService->getOneByUserId($userId);

        foreach($hotel['rooms'] as $index => $room) {
            $stockSchedules = $this->_roomService->getAvailableSchedule($room['id'], $room['amount']);
            $offlineBookSchedules = $this->_scheduleService->getOfflineBookSchedule($room['id']);

            $schedules = array();
            foreach($stockSchedules as $date => $stock) {
                $schedules[$date] = array(
                    'stock' => $stock,
                    'offlineBook' => $offlineBookSchedules[$date]
                );
            }

            $hotel['rooms'][$index]['schedules'] = $schedules;
        }

        $this->_assign('hotel', $hotel);
        $this->_display('merchant/hotel_center');
    }

    public function hotelOrder() {
        $merchantId = intval($_SESSION[SESSION_USER]['id']);

        $params = array(
            'isMerchantIgnore'=>0,
        );

        $order = array(
            'id' => 'desc'
        );

        $pageIndex = intval($_GET['page']) > 0 ? intval($_GET['page']) : 1;
        $pageSize = 3;

        $orderService = OrderService::getInstance();
        $page = $orderService->getHotelOrdersByPage($merchantId, $pageIndex, $pageSize, $params, $order);

        if($page['records']) {
            foreach($page['records'] as $index => $record) {
                $strDate = substr($record['range'], 0, strpos($record['range'], '('));
                $date = strtotime($strDate);
                $date += 86400;
                if($date < time())
                    $page['records'][$index]['isExpiry'] =  true;
                else
                    $page['records'][$index]['isExpiry'] = false;
            }
        }

        $pagination = new Pagination($page);
        $pagination->showSize = 5;
        $pagination->url = SITE_URL.'account/merchant/hotelOrder?page=';
        $paging = $pagination->page();

        $this->_assign('page', $page);
        $this->_assign('paging', $paging);
        $this->_display('merchant/hotel_order');
    }

    public function confirmOrder() {
        $orderId = intval($_GET['order_id']);
        $userId = intval($_SESSION[SESSION_USER]['id']);

        $status = true;
        if($orderId < 1)
            $status = false;

        if($status) {
            $this->_initHotelService();
            $this->_initOrderService();

            $hotel = $this->_hotelService->getOneByUserId($userId);

            if($hotel) {
                $status = $this->_orderService->merchantConfirmOrder($orderId, $hotel['id']);
            }
            else {
                $status = false;
            }
        }

        if($status) {
            $_SESSION[TIPS_TYPE] = TipsType::SUCCESS;
            $_SESSION[TIPS] = '确认订单成功';
        }
        else {
            $_SESSION[TIPS_TYPE] = TipsType::ERROR;
            $_SESSION[TIPS] = '确认订单失败';
        }

        $this->_redirect(SITE_URL.'account/merchant/hotelOrder');
    }

    public function cancelOrder() {
        $orderId = intval($_GET['order_id']);
        $userId = intval($_SESSION[SESSION_USER]['id']);

        $status = true;
        if($orderId < 1)
            $status = false;

        if($status) {
            $this->_initHotelService();
            $this->_initOrderService();

            $hotel = $this->_hotelService->getOneByUserId($userId);

            if($hotel) {
                $status = $this->_orderService->cancelOrderByIdAndHotelId($orderId, $hotel['id']);
            }
            else {
                $status = false;
            }
        }

        if($status) {
            $_SESSION[TIPS_TYPE] = TipsType::SUCCESS;
            $_SESSION[TIPS] = '取消订单成功';
        }
        else {
            $_SESSION[TIPS_TYPE] = TipsType::ERROR;
            $_SESSION[TIPS] = '取消订单失败';
        }

        $this->_redirect(SITE_URL.'account/merchant/hotelOrder');
    }

    public function ignoreOrder() {
        $orderId = intval($_GET['order_id']);
        $userId = intval($_SESSION[SESSION_USER]['id']);

        $status = true;
        if($orderId < 1)
            $status = false;

        if($status) {
            $this->_initHotelService();
            $this->_initOrderService();

            $hotel = $this->_hotelService->getOneByUserId($userId);

            if($hotel) {
                $status = $this->_orderService->ignoreOrderByIdAndHotelId($orderId, $hotel['id']);
            }
            else {
                $status = false;
            }
        }

        if($status) {
            $_SESSION[TIPS_TYPE] = TipsType::SUCCESS;
            $_SESSION[TIPS] = '删除订单成功';
        }
        else {
            $_SESSION[TIPS_TYPE] = TipsType::ERROR;
            $_SESSION[TIPS] = '删除订单成功';
        }

        $this->_redirect(SITE_URL.'account/merchant/hotelOrder');
    }

    public function codeConfirm() {
        $code = trim($_POST['code']);
        $userId = intval($_SESSION[SESSION_USER]['id']);

        $status = true;
        if(strlen($code) == 0)
            $status = false;

        if($status) {
            $this->_initHotelService();
            $this->_initOrderService();

            $hotel = $this->_hotelService->getOneByUserId($userId);

            if($hotel) {
                $status = $this->_orderService->codeConfirm($code, $hotel['id']);
            }
            else {
                $status = false;
            }
        }

        if($status) {
            $_SESSION[TIPS_TYPE] = TipsType::SUCCESS;
            $_SESSION[TIPS] = '验证码正确！';

            $order = $this->_orderService->getOneByCode($code);

            if($order) {
                $this->_assign('order' ,$order);
                $this->_display('merchant/code_result');
            }
        }

        if(!$status) {
            $_SESSION[TIPS_TYPE] = TipsType::ERROR;
            $_SESSION[TIPS] = '验证码有误！';
            $this->_redirect(SITE_URL.'account/merchant/hotelCenter');
        }

    }
}