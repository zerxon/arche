<?php
/**
 * 
 * @author: wallace wallaceleung@163.com
 * @date: 14-4-26
 */

import('Library.Core.DB.ORM');

abstract class Model {

    //表名
    protected $_tableName;

    //模型字段的主键, 默认为id
    protected $_primaryKey = 'id';

    //模型字段对应数据库表字段
    protected $_fields = array(
        /*
        '[fieldName]'=>'[tableFieldName]',
        */
    );

    //模型字段值的数组
    protected $_fieldValues = array();

    //映射对象
    protected $_mappers = array(
        /*
        '[mapperFiledName]'=>array(
            'Type'=>'[hasMany | hasOne]',
            'Fetch'=>[FetchType::EAGER | FetchType::LAZY],
            'Target'=>'[targetTableName]',
            'Mapping'=>array(
                '[sourceField]'=>'[targetFiled]'
            )
        ),
        */
    );

    /**
     * 用于获取model的属性
     * @param $property
     * @return mixed
     */
    protected function _getter($property) {
        $classProperty = '_'.$property;
        if(property_exists($this,$classProperty)) {
            return $this->$classProperty;
        }
        elseif(isset($this->_fieldValues[$property])) {
            return $this->_fieldValues[$property];
        }
        elseif(array_key_exists($property, $this->_mappers)) {
            return null;
        }
        else {
            error($property.' undefined property');
        }
    }

    /**
     * 用于设置model的属性
     * @param $property
     * @param $value
     */
    protected function _setter($property, $value) {
        $classProperty = '_'.$property;
        if(property_exists($this,$classProperty)) {
            $this->$classProperty = $value;
        }
        elseif(array_key_exists($property,$this->_fields)) {
            $this->_fieldValues[$property] = $value;
        }
        elseif(array_key_exists($property,$this->_mappers)) {
            $this->_fieldValues[$property] = $value;
        }
        else {
            error($property.' undefined property');
        }
    }

    public function __call($method, $args) {

        $type = substr($method,0,3);
        $property = str_replace($type, '', $method);
        $property = strtolower(substr($property,0,1)).substr($property,1,strlen($property) - 1);

        if($type == 'get') {
            return $this->_getter($property);
        }
        elseif($type == 'set') {
            if(count($args) == 1) {
                $this->_setter($property, $args[0]);
            }
            else {
                error("method '".$method."' should give one parameter");
            }
        }
        else {
            $property = $method;

            if(count($args) == 0) {
                return $this->_getter($property);
            }
            elseif(count($args) == 1) {
                $this->_setter($property, $args[0]);
            }
            else {
                error("method '".$method."' not found'");
            }
        }
    }

    public function getTableName() {
        $dbConfig = C('db');
        return  $dbConfig['prefix'].$this->_tableName;
    }

    /**
     * 将实体对象的字段属性值转换成数组
     * @return array
     */
    public function toArray() {
        $array = array();
        if(count($this->_fieldValues) > 0) {
            foreach($this->_fieldValues as $field=>$value) {
                if(is_array($value)) {
                    $items = array();
                    foreach($value as $item) {
                        array_push($items, $item->toArray());
                    }

                    $array[$field] = $items;
                }
                elseif(is_object($value)) {
                    $array[$field] = $value->toArray();
                }
                else {
                    $array[$field] = $value;
                }
            }
        }

        return $array;
    }

    /**
     * 批量将实体对象转为数组
     * @param $entities
     * @return array
     */
    public static function entitiesToArray($entities) {
        $array = array();

        if(count($entities) > 0) {
            foreach($entities as $entity) {
                array_push($array, $entity->toArray());
            }
        }

        return $array;
    }
}

class FetchType {
    const LAZY = 0;
    const EAGER = 1;
}