<?php
/**
 * 
 * @author: wallace wallaceleung@163.com
 * @date: 14-5-15
 */

class BlankService {
    private $_BlankORM;

    public function __construct() {
        $this->_BlankORM = new ORM('Blank');
    }

    public function getTopBlanksByStoreId($storeId) {

        $topBlanks = $this->_BlankORM->selectAll()
            ->fetch('blanks','menus')
            ->where()
                ->field('storeId')->eq($storeId)
                ->andField('parentId')->eq(0)
            ->queryAll();

        return $topBlanks;
    }
}