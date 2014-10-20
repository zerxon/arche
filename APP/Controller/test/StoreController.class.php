<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-4-20
 * Time: 下午8:31
 */

import('Model.entity.Store');

class StoreController extends Controller {

    private $_orm;

    public function __construct() {
        $this->_orm = new ORM('store');
    }

    public function index() {
        $var = 'test';
        $this->_assign('title', 'Article');
        $this->_assign('content', $var);
        $this->_display('article');
    }

    public function redirect() {
        $this->_redirect('store', 'selectOne', array('store_id'=>1));
    }

    public function storeList() {
        $orm = new ORM('Store');
        /*
        $store = $orm->select('name','contact','address')
            ->fetch('blanks')
            ->where()
                ->field('id')->eq(1)
                ->andField('contact')->eq('682547')
            ->queryOne();
        */
        //$stores = $db->select('name','contact','address')->queryAll();
        //$stores = $db->selectAll()->queryAll();

        //$blanks = $store->getBlanks();
        //debug($store);

        //delete

    }

    public function selectAll() {
        $stores = $this->_orm->select('name','contact','address')
        ->fetch('blanks')
        ->orderBy(array(
                'name'=>ORDER_TYPE::DESC,
                'contact'=>ORDER_TYPE::ASC
            ))
        ->queryAll();

        //debug($stores[0]->getBlanks());
        debug($stores[0]->blanks());
    }

    public function selectOne() {

        $storeId = $_GET['store_id'];
        $store = $this->_orm->select('name','contact','address')
            ->fetch('blanks')
            ->where()
                ->field('id')->eq($storeId)
                ->andField('contact')->eq('682547')
            ->queryOne();

        debug($store);
    }

    public function insert() {
        $array = array(
            'name'=>'ewrwer',
            'address'=>'ewrewr',
            'contact'=>'8909'
        );

        $result = $this->_orm->insert()->fieldsWithValues($array)->execute();
        debug($result);
    }

    public function update() {
        $array = array(
            'name'=>'abc',
            'address'=>'new road'
        );

        $result = $this->_orm->update()->fieldsWithValues($array)->where()->field('id')->eq(4)->execute();
        debug($result);
    }

    public function delete() {
        $result = $this->_orm->delete()->where()->field('id')->eq(3)->andField('contact')->eq('123456')->execute();
        debug($result);
    }

    public function arSave() {
        $store = new Store();
        $store->setId(6);
        $store->setName('ar insert');
        $store->setAddress('new abc address');
        $store->setContact('123456');

        $store->save();
    }

    public function arDelete() {
        $store = new Store();
        $store->setId(6);
        $store->setName('ar insert');
        $store->setAddress('new abc address');
        $store->setContact('123456');

        $status = $store->delete();

        debug($status);
    }

    public function arFindOne() {
        $id = intval($_GET['id']);

        $store = new Store();
        $store->findOne($id);

        debug($store);
    }

    public function filter() {
        echo 'access ok';
    }

}