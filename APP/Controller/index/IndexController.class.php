<?php
/**
 *
 * @author: wallace wallaceleung@163.com
 * @date: 14-11-03
 */

import('Model.service.HotelService');

class IndexController extends Controller {

    private $_hotelService;

    public function __construct() {
        $this->_hotelService = HotelService::getInstance();
    }

    public function index() {

        $hotelsPage = $this->_hotelService->getHotelsByPage(1, 10);

        $this->_assign('hotels', $hotelsPage['records']);
        $this->_display('index/index');
    }

}