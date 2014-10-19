<?php
/**
 * 
 * @author: wallace wallaceleung@163.com
 * @date: 14-5-12
 */

import('Model.entity.Store');

class StoreService {
    private $_storeORM;

    private static $_instance;

    public static function getInstance() {
        if(self::$_instance == null) {
            self::$_instance = new self;
        }

        return self::$_instance;
    }

    public function __construct() {
        $this->_storeORM = new ORM('store');
    }

    public function getAvailableStoresByPage($pageIndex, $pageSize) {

        $stores = $this->_storeORM->selectAll()
            ->where()
                ->field('isShow')->eq(1)
            ->orderBy(array('id'=>ORDER_TYPE::ASC))
            ->page($pageIndex,$pageSize)
            ->queryAll();

        return $stores;
    }

    public function getAllStoresByPage($pageIndex, $pageSize) {
        $stores = $this->_storeORM->selectAll()
            ->page($pageIndex, $pageSize)
            ->queryAll();

        return $stores;
    }

    public function getStoreById($storeId) {
        $store = new Store();
        $store->findOne($storeId);
        /*
        $store->findOne(array(
            'id'=>1,
            'contact'=>'682547'
        ));
        */

        return $store;
    }

}