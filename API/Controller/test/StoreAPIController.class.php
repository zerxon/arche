<?php
/**
 * 
 * @author: wallace wallaceleung@163.com
 * @date: 14-5-10
 */

import('Model.service.StoreService');
import('Model.service.BlankService');

class StoreAPIController extends APIController {

    private $_storeService;
    private $_blankService;

    public function __construct() {
        $this->_storeService = new StoreService();
        $this->_blankService = new BlankService();
    }

    public function all() {
        $pageIndex = 1;
        $pageSize = 6;
        $stores = $this->_storeService->getAvailableStoresByPage($pageIndex, $pageSize);

        $items = Model::entitiesToArray($stores);

        $this->_data = array(
            'pageIndex'=>$pageIndex,
            'pageSize'=>$pageSize,
            'items'=>$items
        );

    }

    public function detail() {
        $storeId = intval($_GET['store_id']);

        if($storeId > 0) {
            $store = $this->_storeService->getStoreById($storeId);
            if(!$store->isEmpty()) {
                $topBlanks = $this->_blankService->getTopBlanksByStoreId($store->id());
                $store->blanks($topBlanks);
            }

            $this->_data = $store->toArray();
        }
        else {
            $this->_success = false;
            $this->_error = API_ERROR_TYPE::INVALID_PARAMS;
        }
    }
}