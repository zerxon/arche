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
    private $_operatorType;
    private $_limit;

    private $groupBy;

    private $_fetchs = array();

    private $_orders = array();
    private $_page = array();

    public function __construct($className) {
        $dbConfig = C('db');

        $this->sql = '';
        $this->_reflection = new ModelReflection($className);
        $this->_tableName = $this->_reflection->getTableName();

        $this->_driver = MysqlDriver::getInstance($dbConfig);
        //$this->_driver = new MysqlDriver($dbConfig); //需要修改
    }

    private function _operator($sign, $value) {
        $value = slashes($value);

        $this->_sql .= "$sign'$value'";
    }

    private function _page($pageIndex, $pageSize) {
        $status = false;
        if(is_numeric($pageIndex) && is_numeric($pageSize) && $pageIndex > 0 && $pageSize > 0)
            $status = true;

        if($status) {
            $this->_page = array(
                'pageIndex'=>intval($pageIndex),
                'pageSize'=>intval($pageSize)
            );
        }
        else {
            error("pageIndex or pageSize must greater than 0");
        }

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

        //group by
        if ($this->groupBy) {
            $this->_sql .= " GROUP BY `".$this->groupBy."` ";
        }

        //order by
        if(count($this->_orders) > 0) {
            $orderBy = ' ORDER BY ';
            $orderByArray = array();
            foreach($this->_orders as $field=>$sort) {
                
                if (is_string($field)) {
                    $tableFieldWithTableName = $this->_reflection->getTableFieldWithTableName($field);
                    $str = "$tableFieldWithTableName $sort";
                }
                else {
                    $str = $sort;
                }
                              
                array_push($orderByArray, $str);
            }

            $orderBy .= implode(',', $orderByArray);
            $this->_sql .= $orderBy;
        }

        //page
        if(count($this->_page) > 0) {
            $offset = ($this->_page['pageIndex'] - 1) * $this->_page['pageSize'];

            $this->_sql .= " LIMIT $offset,".$this->_page['pageSize'];
        }
        else if ($this->_limit) {
            $this->_sql .= $this->_limit;
        }
    }

    private function _modelMapping($object, $fetchs, $reflection = null, $parentMapperKey = null) {
        if($reflection == null)
            $reflection = $this->_reflection;

        if(count($fetchs) > 0)
            $reflection->setMappersActive($fetchs, $parentMapperKey);
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

            $targetClass = $mapper['Target'];
            $targetReflection = new ModelReflection($targetClass);
            $targetTableName = $targetReflection->getTableName();

            //查询条件
            $mappingConditionArray = array();
            foreach($mapper['Mapping'] as $field=>$targetField) {
                $targetTableFieldWithTableName = $targetReflection->getTableField($targetField);

                $getFieldValue = 'get'.ucfirst($field);
                $str = "`$targetTableFieldWithTableName`='".slashes($object->$getFieldValue())."'";
                array_push($mappingConditionArray, $str);
            }

            $mappingCondition = implode(' AND ', $mappingConditionArray);

            //排序
            if(!empty($mapper['Order'])) {
                if (!is_array($mapper['Order'])) {
                    $order = explode(' ', $mapper['Order']);
                    $orderField = trim($order[0]);
                    $orderType = trim($order[1]);
                    $mappingCondition .= "ORDER BY ".$targetReflection->getTableFieldWithTableName($orderField)." $orderType";
                }
                else {
                    $mappingCondition .= "ORDER BY ".$mapper['Order'][0];
                }

                
            }

            //查询字段
            $targetQueryString = $this->_queryString($targetReflection->getFields(), $targetReflection);
            $sql = "SELECT $targetQueryString FROM $targetTableName WHERE $mappingCondition";

            //设置关联映射对象的方法名
            $setMappingField = 'set'.ucfirst($key);

            //判断类型
            if($mapper['Type'] == 'hasMany') {
                $targetRecords = $this->_driver->fetch_all_assoc($sql);

                if($targetRecords) {
                    $targetObjects = $targetReflection->parseAll($targetRecords);

                    //级联取出下一级关联映射对象
                    if($targetObjects) {
                        $targetObject0 = $targetObjects[0];
                        $subReflection = new ModelReflection($targetObject0);
                        foreach($targetObjects as $index=>$targetObject) {
                            $this->_modelMapping($targetObjects[$index], $fetchs, $subReflection, $parentMapperKey);
                        }
                        unset($subReflection);
                    }

                    $object->$setMappingField($targetObjects);
                }
                else {
                    $object->$setMappingField(null);
                }
            }
            elseif($mapper['Type'] == 'hasOne') {
                $targetRecord = $this->_driver->once_fetch_assoc($sql);

                if($targetRecord) {
                    $targetObject = $targetReflection->parse($targetRecord);

                    //级联取出下一级关联映射对象
                    $subReflection = new ModelReflection($targetObject);
                    $this->_modelMapping($targetObject, $fetchs, $subReflection, $parentMapperKey);
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
        $this->_operatorType = SQL_OPERATOR_TYPE::SELECT;

        $fields = $this->_reflection->getFields();
        $queryString = $this->_queryString($fields);

        $this->_sql = "SELECT $queryString FROM `$this->_tableName`";

        return $this;
    }

    public function select() {
        $argsCount = func_num_args();
        $args = func_get_args();

        $this->_operatorType = SQL_OPERATOR_TYPE::SELECT;

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
            error("ORM `fetch` must give at least one parameter");
        }

        return $this;
    }

    public function update() {
        $this->_operatorType = SQL_OPERATOR_TYPE::UPDATE;
        $this->_sql = "UPDATE $this->_tableName SET";

        return $this;
    }

    public function insert() {
        $this->_operatorType = SQL_OPERATOR_TYPE::INSERT;
        $this->_sql = "INSERT INTO $this->_tableName SET";

        return $this;
    }

    public function delete() {
        $this->_operatorType = SQL_OPERATOR_TYPE::DELETE;
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

    public function where($params = null, $type = 'AND') {
        $this->_sql .= " WHERE";

        if(is_array($params) && count($params) > 0) {
            $index = 0;
            foreach($params as $filed => $value) {
                if($index == 0) {
                    $this->field($filed);
                }
                else {
                    if(strtoupper($type) == 'AND')
                        $this->andField($filed);
                    else if(strtoupper($type) == 'OR')
                        $this->orField($filed);
                    else
                        error("where condition type only allow AND/OR");
                }

                if (is_array($value)) {
                    $op = $value[0];
                    $this->$op($value[1]);
                }
                else {
                    $this->eq($value);
                }

                $index++;
            }
        }
        else {
            $this->_sql .= " $params ";
        }

        return $this;
    }

    public function field($field) {
        $tableFieldWithTableName = $this->_reflection->getTableFieldWithTableName($field);
        $this->_sql .= " $tableFieldWithTableName";

        return $this;
    }

    public function nextField($field) {
        $tableFieldWithTableName = $this->_reflection->getTableFieldWithTableName($field);
        $this->_sql .= ",$tableFieldWithTableName";

        return $this;
    }

    public function andString($str) {
        $this->_sql .= " AND $str ";
        return $this;
    }

    public function orString($str) {
        $this->_sql .= " OR $str ";
        return $this;
    }

    public function andField($field) {
        $tableFieldWithTableName = $this->_reflection->getTableFieldWithTableName($field);
        $this->_sql .= " AND $tableFieldWithTableName";

        return $this;
    }

    public function orField($field) {
        $tableFieldWithTableName = $this->_reflection->getTableFieldWithTableName($field);
        $this->_sql .= " OR $tableFieldWithTableName";

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

    public function groupBy($groupBy) {
        $this->groupBy = $groupBy;

        return $this;
    }

    public function orderBy($orders) {
        $this->_orders = $orders;

        return $this;
    }

    public function limit($start, $end = null) {
        $start = intval($start);
        $end == null ? null : intval($end);

        if($start >= 0 && ($end == null || $end > 0)) {
            if($end != null)
                $limit = " LIMIT $start, $end";
            else
                $limit = " LIMIT $start";

            $this->_limit .= $limit;
        }

        return $this;
    }

    public function execute() {
        $result = $this->_driver->query($this->_sql);

        $status = false;
        if($result)
            $status = true;

        return $status;
    }

    public function queryAffectedRows() {
        $result = $this->_driver->query($this->_sql);

        $rows = 0;
        if($result)
            $rows = $this->_driver->affected_rows();

        return $rows;
    }

    public function queryNumRows() {
        return $this->_driver->once_num_rows($this->_sql);
    }

    public function queryOne($isObj = false, $isOrigin = false) {
        $this->_processSQLString();
        $record = $this->_driver->once_fetch_assoc($this->_sql);

        //判断是否返回原始数据
        if($isOrigin)
            return $record;

        if($record) {
            $object = $this->_reflection->parse($record);
            $this->_modelMapping($object, $this->_fetchs);

            if($isObj) {
                return $object;
            }
            else {
                return $object->toArray();
            }
        }
        else {
            return null;
        }
    }

    public function querySql($sql) {
        $records = $this->_driver->fetch_all_assoc($sql);

        return $records;
    }

    public function queryAll($isObj = false, $isOrigin = false) {
        $this->_processSQLString();
        $records = $this->_driver->fetch_all_assoc($this->_sql);

        //判断是否返回原始数据
        if($isOrigin)
            return $records;

        if(count($records) > 0) {
            $objects = $this->_reflection->parseAll($records);

            foreach($objects as $object) {
                $this->_modelMapping($object, $this->_fetchs);
            }

            if($isObj) {
                return $objects;
            }
            else {
                return Model::entitiesToArray($objects);
            }
        }
        else {
            return null;
        }
    }

    public function queryPage($pageIndex, $pageSize) {
        $this->_page($pageIndex, $pageSize);

        $records = $this->queryAll();

        $sql = reset(explode(' LIMIT', $this->_sql));
        $sql = end(explode(' FROM ', $sql));
        $sql = 'select count(*) FROM '.$sql;

        $totalRecords = $this->_driver->get_value($sql);
        $totalRecords = intval($totalRecords);

        //页面数量
        $totalRecords % $pageSize == 0 ?  $p = 0 : $p = 1;
        $totalPages = floor($totalRecords / $pageSize) + $p;

        $pageModel = array(
            'pageIndex' => $pageIndex,
            'pageSize' => $pageSize,
            'totalPages' => $totalPages,
            'totalRecords'=> $totalRecords,
            'records' => $records
        );

        return $pageModel;
    }

}

class ORDER_TYPE {
    const ASC = 'ASC';
    const DESC = 'DESC';
}

class SQL_OPERATOR_TYPE {
    const SELECT = 'select';
    const INSERT = 'insert';
    const UPDATE = 'update';
    const DELETE ='delete';
}