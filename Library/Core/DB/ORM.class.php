<?php
/**
 * DB类
 * @author: wallace wallaceleung@163.com
 * @date: 14-4-26
 */

import('Library.Core.DB.MysqlDriver');
import('Library.Core.Model.ModelReflection');

class ORM {

    private $_driver;
    private $_sql;
    private $_reflection;
    private $_tableName;

    private $_fetchs = array();

    private $_orders = array();
    private $_page = array();

    public function __construct($className) {
        $this->sql = '';
        $this->_reflection = new ModelReflection($className);
        $this->_tableName = $this->_reflection->getTableName();

        $dbConfig = C('db');
        $this->_driver = MysqlDriver::getInstance($dbConfig);
        //$this->_driver = new MysqlDriver($dbConfig); //需要修改
    }

    private function _operator($sign, $value) {
        $value = slashes($value);

        $this->_sql .= "$sign'$value'";
    }

    private function _queryString($tableFields, $reflection = null) {
        if($reflection == null) {
            $reflection = $this->_reflection;
        }

        $queryArray = array();
        foreach($tableFields as $tableField) {
            $alias = $reflection->getTableFieldAlias($tableField);
            $query = "`".$reflection->getTableName()."`.`$tableField` AS $alias";
            array_push($queryArray, $query);
        }

        $queryString = implode(',', $queryArray);
        return $queryString;
    }

    private function _processSQLString() {

        //order by
        if(count($this->_orders) > 0) {
            $orderBy = ' ORDER BY ';
            $orderByArray = array();
            foreach($this->_orders as $field=>$sort) {
                $tableFieldWithTableName = $this->_reflection->getTableFieldWithTableName($field);

                $str = "$tableFieldWithTableName $sort";
                array_push($orderByArray, $str);
            }

            $orderBy .= implode(',', $orderByArray);
            $this->_sql .= $orderBy;
        }

        //page
        if(count($this->_page) > 0) {
            $offset = ($this->_page['pageIndex'] - 1) * $this->_page['pageSize'];

            $this->_sql .= " limit $offset,".$this->_page['pageSize'];
        }
    }

    private function _modelMapping($object, $reflection = null, $parentMapperKey = null) {
        if($reflection == null) {
            $reflection = $this->_reflection;
        }

        if(count($this->_fetchs) > 0)
            $reflection->setMappersActive($this->_fetchs, $parentMapperKey);
        else
            return;

        $activeMappers = $reflection->getMappers(true);

        if(!$activeMappers || count($activeMappers) < 1)
            return;

        foreach($activeMappers as $key=>$mapper) {
            if($parentMapperKey != null)
                $parentMapperKey .= ".".$key;
            else
                $parentMapperKey = $key;

            $targetClass = $mapper['target'];
            $targetReflection = new ModelReflection($targetClass);
            $targetTableName = $targetReflection->getTableName();

            //查询条件
            $mappingConditionArray = array();
            foreach($mapper['mapping'] as $field=>$targetField) {
                $targetTableFieldWithTableName = $targetReflection->getTableField($targetField);

                $getFieldValue = 'get'.ucfirst($field);
                $str = "$targetTableFieldWithTableName=".$object->$getFieldValue();
                array_push($mappingConditionArray, $str);
            }

            /*
            if($mapper['condition']) {
                foreach($mapper['condition'] as $sourceField=>$target) {
                    $sourceTableField = $reflection->getTableField($sourceField);
                    $mappingCondition .= "$sourceTableField=".$object->getId()." AND ";
                    //debug($mappingCondition);
                }
            }
            */

            $mappingCondition = implode(' AND ', $mappingConditionArray);

            //查询字段
            $targetQueryString = $this->_queryString($targetReflection->getFields(), $targetReflection);
            $sql = "SELECT $targetQueryString FROM $targetTableName WHERE $mappingCondition";

            //设置关联映射对象的方法名
            $setMappingField = 'set'.ucfirst($key);

            //判断类型
            if($mapper['type'] == 'hasMany') {
                $targetRecords = $this->_driver->fetch_all_assoc($sql);

                if($targetRecords) {
                    $targetObjects = $targetReflection->parseAll($targetRecords);

                    //级联取出下一级关联映射对象
                    if($targetObjects) {
                        $targetObject0 = $targetObjects[0];
                        $subReflection = new ModelReflection($targetObject0);
                        foreach($targetObjects as $index=>$targetObject) {
                            $this->_modelMapping($targetObjects[$index], $subReflection, $parentMapperKey);
                        }
                        unset($subReflection);
                    }

                    $object->$setMappingField($targetObjects);
                }
                else {
                    $object->$setMappingField(null);
                }
            }
            elseif($mapper['type'] == 'hasOne') {
                $targetRecord = $this->_driver->once_fetch_assoc($sql);

                if($targetRecord) {
                    $targetObject = $targetReflection->parse($targetRecord);

                    //级联取出下一级关联映射对象
                    $subReflection = new ModelReflection($targetObject);
                    $this->_modelMapping($targetObject, $subReflection, $parentMapperKey);
                    unset($subReflection);

                    $object->$setMappingField($targetObject);
                }
                else{
                    $object->$setMappingField(null);
                }
            }
        }
    }

    public function selectAll() {
        $fields = $this->_reflection->getFields();
        $queryString = $this->_queryString($fields);

        $this->_sql = "SELECT $queryString FROM `$this->_tableName`";

        return $this;
    }

    public function select() {
        $argsCount = func_num_args();
        $args = func_get_args();

        if($argsCount > 0) {
            //主键默认查询出来
            $pKey = $this->_reflection->getPrimaryKey();
            array_unshift($args, $pKey);

            $tableFields = array();
            foreach($args as $arg) {
                $tableField = $this->_reflection->getTableField($arg);
                array_push($tableFields, $tableField);
            }

            $queryString = $this->_queryString($tableFields);

            $this->_sql = "SELECT $queryString FROM `$this->_tableName`";
        }
        else {
            error("ORM `select` must give least one parameter");
        }

        return $this;
    }

    public function fetch() {
        $argsCount = func_num_args();
        $this->_fetchs = func_get_args();

        if($argsCount == 0) {
            error("ORM `fetch` must give least one parameter");
        }

        return $this;
    }

    public function update() {
        $this->_sql = "UPDATE $this->_tableName SET";

        return $this;
    }

    public function insert() {
        $this->_sql = "INSERT INTO $this->_tableName SET";

        return $this;
    }

    public function delete() {
        $this->_sql = "DELETE FROM $this->_tableName";

        return $this;
    }

    public function fieldsWithValues($array) {
        $items = array();
        foreach($array as $field=>$value) {
            $tableFieldWithTableName = $this->_reflection->getTableFieldWithTableName($field);
            $value = slashes($value);

            $items[] = "$tableFieldWithTableName='$value'";
        }

        $str = implode(',', $items);
        $this->_sql .= " $str";

        return $this;
    }

    public function where() {
        $this->_sql .= " WHERE";

        return $this;
    }

    public function field($field) {
        $tableFieldWithTableName = $this->_reflection->getTableFieldWithTableName($field);
        $this->_sql .= " $tableFieldWithTableName";

        return $this;
    }

    public function andField($field) {
        $tableFieldWithTableName = $this->_reflection->getTableFieldWithTableName($field);
        $this->_sql .= " AND $tableFieldWithTableName";

        return $this;
    }

    public function eq($value) {
        $this->_operator('=', $value);

        return $this;
    }

    public function neq($value) {
        $this->_operator('<>', $value);

        return $this;
    }

    public function gt($value) {
        $this->_operator('>',$value);

        return $this;
    }

    public function ge($value) {
        $this->_operator('>=',$value);

        return $this;
    }

    public function lt($value) {
        $this->_operator('<',$value);

        return $this;
    }

    public function le($value) {
        $this->_operator('<=',$value);

        return $this;
    }

    public function like($value) {
        $value = slashes($value);
        $this->_sql .= " LIKE '%$value%'";

        return $this;
    }

    public function sql($sql) {
        $this->_sql = $sql;

        return $this;
    }

    public function orderBy($orders) {
        $this->_orders = $orders;

        return $this;
    }

    public function page($pageIndex, $pageSize) {
        $this->_page = array(
            'pageIndex'=>intval($pageIndex),
            'pageSize'=>intval($pageSize)
        );

        return $this;
    }

    public function execute() {
        $result = $this->_driver->query($this->_sql);
        //$this->_driver->affected_rows();

        $status = false;
        if($result)
            $status = true;

        return $status;
    }

    public function queryOne() {
        $this->_processSQLString();
        $record = $this->_driver->once_fetch_assoc($this->_sql);
        if($record) {
            $object = $this->_reflection->parse($record);
            $this->_modelMapping($object);

            return $object;
        }
        else {
            return null;
        }
    }

    public function queryAll() {
        $this->_processSQLString();
        $records = $this->_driver->fetch_all_assoc($this->_sql);

        if(count($records) > 0) {
            $objects = $this->_reflection->parseAll($records);

            foreach($objects as $object) {
                $this->_modelMapping($object);
            }

            return $objects;
        }
        else {
            return null;
        }
    }

}

class ORDER_TYPE {
    const ASC = 'ASC';
    const DESC = 'DESC';
}