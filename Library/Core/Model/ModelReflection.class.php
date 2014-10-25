<?php
/**
 * 
 * @author: wallace wallaceleung@163.com
 * @date: 14-4-27
 */

class ModelReflection {

    private $_className;
    private $_class;
    private $_instance;

    public function __construct($class) {
        try {
            if(is_object($class)) {
                $this->_class = new ReflectionObject($class);
                $this->_instance = $class;
                $this->_className = $this->_class->getName();
            }
            else {
                $this->_className = $class;
                $this->_class = new ReflectionClass($class);
                $this->_instance = $this->_class->newInstance();
            }
        }catch (Exception $ex) {
            error($ex->getMessage());
        }
    }

    public function getClassName() {
        return $this->_className;
    }

    public function getTableName() {
        return $this->_instance->getTableName();
    }

    public function getPrimaryKey() {
        return $this->_instance->getPrimaryKey();
    }

    public function getFields() {
        return $fields = $this->_instance->getFields();
    }

    public function getTableField($key) {
        $fields = $this->getFields();

        if(!isset($fields[$key])) {
            error("The Field '$key' of Model '".$this->getTableName()."' doesn't exist");
            return false;
        }

        return $fields[$key];
    }

    public function getTableFieldWithTableName($key) {
        $tableField = $this->getTableField($key);
        $var = "`".$this->getTableName()."`.`$tableField`";

        return $var;
    }

    public function getTableFieldAlias($tableField) {
        $tableName = $this->getTableName();
        $alias = $tableName.'_'.$tableField;

        return $alias;
    }

    public function getMappers($isActive = false) {
        $allMappers = $this->_instance->getMappers();

        if($isActive) {
            $activeMappers = array();
            foreach($allMappers as $key=>$mapper) {
                if($mapper['fetch']) {
                    $activeMappers[$key] = $mapper;
                }
            }

            return $activeMappers;
        }
        else {
            return $allMappers;
        }
    }

    public function setMappersActive($key, $parentMapperKey = null) {
        $mappers = $this->getMappers();

        if(is_array($key)) {
            foreach($key as $index=>$mapperName) {
                if(isset($mappers[$mapperName])) {
                    $mappers[$mapperName]['fetch'] = FetchType::EAGER;
                    //unset($key[$index]);
                }
                elseif(strstr($mapperName, '.') || $parentMapperKey != null) {
                    if($parentMapperKey != null) {
                        //debug($this->_className." ".$parentMapperKey." ".$mapperName, false);
                        $mapperName = str_replace($parentMapperKey.'.', '', $mapperName);
                        if(isset($mappers[$mapperName]))
                            $mappers[$mapperName]['fetch'] = FetchType::EAGER;
                    }
                }
                else{
                    error("Model ".$this->_class->getName()."'s mapper '$mapperName' does not exist");
                }
            }
        }
        else {
            if(isset($mappers[$key])) {
                $mappers[$key]['fetch'] = FetchType::EAGER;
            }
            else {
                error("Model mapper '$key' does not exist");
            }
        }

        $this->_instance->setMappers($mappers);
    }

    public function parse($record) {
        $fields = $this->getFields();

        $instance = $this->_class->newInstance();
        foreach($fields as $field=>$tableField) {
            $alias = $this->getTableFieldAlias($tableField);
            if(isset($record[$alias])) {
                $setter = 'set'.ucfirst($field);
                $instance->$setter($record[$alias]);
            }
        }

        return $instance;
    }

    public function parseAll($records) {
        $instances = array();
        foreach($records as $record) {
            $instance = $this->parse($record);
            array_push($instances, $instance);
        }

        return $instances;
    }

}