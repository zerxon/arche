<?php
/**
 * 
 * @author: wallace wallaceleung@163.com
 * @date: 14-10-24
 */

import('Model.service.RoomService');
import('Library.Ext.DWZJson');

class RoomController extends Controller {

    private $_roomService;

    public function __construct() {
        $this->_roomService = RoomService::getInstance();
    }

    public function all() {
        $pageIndex = intval($_POST['pageNum']);
        $pageSize = intval($_POST['numPerPage']);

        if(!$pageIndex)
            $pageIndex = 1;

        if(!$pageSize)
            $pageSize = 30;

        $roomsPage = $this->_roomService->getRoomsByPage($pageIndex, $pageSize);

        $this->_assign('page', $roomsPage);
        $this->_display('admin/room_all');
    }

    public function edit() {
        $id = intval($_GET['room_id']);

        if($id > 0) {
            $room = $this->_roomService->getOneById($id);
            $this->_assign('room', $room);
        }

        $this->_display('admin/room_edit');
    }

    public function doEdit() {
        $id = intval($_POST['room_id']);

        $room = new Room();

        $room->id($id);
        $room->name($_POST['name']);
        $room->hotelId($_POST['hotel_id']);
        $room->price(floatval($_POST['price']));
        $room->amount(intval($_POST['amount']));
        //$room->stock(intval($_POST['stock']));
        $room->desc($_POST['desc']);

        /*
        if($id == 0) {
            $room->stock($room->amount());
        }
        */

        $result = $this->_roomService->save($room);

        $jsonObj = new DWZJson();

        if($result) {
            $jsonObj->statusCode = DWZStatusCode::SUCCESS;
            $jsonObj->message = '操作成功';
            $jsonObj->navTabId = 'room_all';

            if($id == 0)
                $jsonObj->callbackType = DWZCallBackType::CLOSE_CURRENT;
        }
        else {
            $jsonObj->message = '操作失败';
        }

        $this->_output($jsonObj, 'json');
    }

    public function delete() {
        $id = $_GET['room_id'];
        $status = $this->_roomService->delete($id);

        $jsonObj = new DWZJson();

        if($status) {
            $jsonObj->statusCode = DWZStatusCode::SUCCESS;
            $jsonObj->message = '删除成功';
            $jsonObj->navTabId = 'room_all';
        }
        else {
            $jsonObj->message = '删除失败';
        }

        $this->_output($jsonObj, 'json');
    }
}