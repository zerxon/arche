<?php
/**
 * 
 * @author: wallace wallaceleung@163.com
 * @date: 14-5-8
 */

import('Library.Core.DB.MysqlDriver');
import('Library.Core.Model.Model');

abstract class ARModel extends Model {

    private $_driver;
    private $_isEmpty = false;

    private function _parse($record) {
        foreach($this->_fields as $field=>$tableField) {
            if(isset($record[$tableField])) {
                $this->_fieldValues[$field] = $record[$tableField];
            }
        }
    }

    public function __construct() {
        $dbConfig = C('db');
        $this->_driver = MysqlDriver::getInstance($dbConfig);
    }

    public function isEmpty() {
        return $this->_isEmpty;
    }

    public function save() {
        $isNewRecord = true;
        $record = array();

        $where = '';
        $pKeyValue = slashes($this->_fieldValues[$this->_primaryKey]);
        if($this->_fieldValues[$this->_primaryKey]) {
            $isNewRecord = false;
            $where = " WHERE ".$this->_fields[$this->_primaryKey]."='$pKeyValue'";
        }

        foreach($this->_fields as $field=>$tableField) {
            //如果不是主键，或者存放字段的数组中已设置过该值则加入到添加/更新数组中
            if($field != $this->_primaryKey && array_key_exists($field, $this->_fieldValues)) {
                $record[$tableField] = slashes($this->_fieldValues[$field]);
            }
        }

        if($isNewRecord)
            $result = $this->_driver->insertArr($record, $this->getTableName());
        else
            $result = $this->_driver->updateArr($record, $this->getTableName(), $where);

        return $result;
    }

    public function delete() {
        $status = false;
        $pKey = $this->_fields[$this->_primaryKey];
        $pKeyValue = slashes($this->_fieldValues[$this->_primaryKey]);

        if(!$pKeyValue)
            error("primary key value not set");

        $sql = "DELETE FROM $this->getTableName() WHERE $pKey='$pKeyValue'";
        $result = $this->_driver->query($sql);

        if($result)
            $status = true;

        return $status;
    }

    public function findOne($key) {
        $status = false;

        if(is_array($key)) {
            $conditions = array();
            foreach($key as $field=>$value) {
                $str = $this->_fields[$field]."='$value'";
                array_push($conditions, $str);
            }
            $strCondition = implode(' AND ', $conditions);
        }
        elseif(intval($key) > 0){
            $pKey = $this->_fields[$this->_primaryKey];
            $value = intval($key);
            $strCondition = "$pKey='$value'";
        }
        else {
            error('Invalid params');
        }

        $sql = "SELECT * FROM ".$this->getTableName()." WHERE $strCondition";
        $record = $this->_driver->once_fetch_assoc($sql);

        if($record) {
            $status = true;
            $this->_parse($record);
        }
        else {
            $this->_isEmpty = true;
        }

        return $status;
    }

}