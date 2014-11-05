<?php
/**
 * 
 * @author: wallace wallaceleung@163.com
 * @date: 14-10-19
 */

import('Model.service.HotelService');

class HotelController extends Controller {

    private $_hotelService;

    public function __construct() {
        $this->_hotelService = HotelService::getInstance();
    }

    public function detail() {
        $hotelId = intval($_GET['id']);

        if($hotelId > 0)
            $hotel = $this->_hotelService->getOneById($hotelId);

        if(!$hotel)
            error('Invalid hotel id');

        $this->_assign('hotel', $hotel);
        $this->_display('hotel/detail');
    }

}