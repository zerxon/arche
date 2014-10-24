<?php
/**
 * 
 * @author: wallace wallaceleung@163.com
 * @date: 14-10-21
 */

import('Model.service.HotelService');
import('Library.Ext.DWZJson');

class HotelController extends Controller {

    private $_hotelService;

    public function __construct() {
        $this->_hotelService = HotelService::getInstance();
    }

    public function index() {
        debug('admin hotel index action');
    }

    public function all() {
        $pageIndex = $_POST['pageNo'];
        $pageSize = $_POST['numPerPage'];

        if(!$pageIndex)
            $pageIndex = 1;

        if(!$pageSize)
            $pageSize = 10;

        $hotelsPage = $this->_hotelService->getHotelsByPage($pageIndex, $pageSize);

        $this->_assign('page', $hotelsPage);
        $this->_display('admin/hotel_all');
    }

    public function edit() {
        $id = intval($_GET['hotel_id']);

        if($id > 0) {
            $hotel = $this->_hotelService->getOneById($id);
            $this->_assign('hotel', $hotel);
        }

        $this->_display('admin/hotel_edit');
    }

    public function doEdit() {
        $id = intval($_POST['hotel_id']);

        $hotel = new Hotel();

        $hotel->id($id);
        $hotel->name($_POST['name']);
        $hotel->tel($_POST['tel']);
        $hotel->address($_POST['address']);
        $hotel->userId($_POST['user_id']);
        $hotel->isOpening($_POST['is_opening'] == 'on');

        if($id == 0) {
            $hotel->addTime(time());
        }

        $result = $this->_hotelService->save($hotel);

        $jsonObj = new DWZJson();

        if($result) {
            $jsonObj->statusCode = DWZStatusCode::SUCCESS;
            $jsonObj->message = '操作成功';
            $jsonObj->navTabId = 'hotel_all';

            if($id == 0)
                $jsonObj->callbackType = DWZCallBackType::CLOSE_CURRENT;
        }
        else {
            $jsonObj->message = '操作失败';
        }

        $this->_output($jsonObj, 'json');
    }

    public function delete() {
        $id = $_GET['hotel_id'];
        $status = $this->_hotelService->delete($id);

        $jsonObj = new DWZJson();

        if($status) {
            $jsonObj->statusCode = DWZStatusCode::SUCCESS;
            $jsonObj->message = '删除成功';
            $jsonObj->navTabId = 'hotel_all';
        }
        else {
            $jsonObj->message = '删除失败';
        }

        $this->_output($jsonObj, 'json');
    }
}