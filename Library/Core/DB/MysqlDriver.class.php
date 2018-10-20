<?php

import('Library.Core.DB.DBHandler');

class MysqlDriver {

	var $queryCount = 0;
	var $conn;
	var $result;
    var $dbConfig;
    var $dbHandler;

    private static $_instance;
    private static $_connects;

	public function __construct($dbConfig){

		if (!function_exists('mysqli_connect')) {
            error('The setting of PHP on server does not support mysqli');
        }

        if(empty($dbConfig['DB_HOST'])) {
            error("db config 'DB_HOST' not set");
        }
        else if(empty($dbConfig['DB_USER'])) {
            error("db config 'DB_USER' not set");
        }
        else if(empty($dbConfig['DB_PWD'])) {
            error("db config 'DB_PWD' not set");
        }
        else if(empty($dbConfig['DB_NAME'])) {
            error("db config 'DB_NAME' not set");
        }

		$this->dbConfig = $dbConfig;
        $this->dbHandler = new DBHandler();

        //$this->connect();
    }

    public static function getInstance($dbConfig) {
        if(MysqlDriver::$_instance == null)
            MysqlDriver::$_instance = new self($dbConfig);

        return MysqlDriver::$_instance;
    }

    private function connect($sql = '') {
        $dbConfig = $this->dbConfig;
		$this->dbHandler->onConnect($dbConfig, $sql);

        $key = md5(json_encode($dbConfig));
        if (array_key_exists($key, MysqlDriver::$_connects)) {
            $this->conn = MysqlDriver::$_connects[$key];
        }
        else {
            $this->conn = mysqli_connect($dbConfig['DB_HOST'], $dbConfig['DB_USER'], $dbConfig['DB_PWD'],$dbConfig['DB_NAME'])
            or die(mysqli_connect_error());

            if ($this->getmysqlVersion() >'4.1'){
                mysqli_query($this->conn,"SET NAMES 'utf8'");
            }

            MysqlDriver::$_connects[$key] = $this->conn;
        }

    }

	/*
	 *关闭数据库
	 */
	 
	function close(){
		return mysqli_close($this->conn);
	}

	/*
	 *发送查询语句
	 */
	 
	function query($sql){
        $this->dbHandler->onExecute($sql);
		$this->connect($sql);

		$this->result = mysqli_query($this->conn, $sql);
		$this->queryCount++;
	    if (!$this->result){
			if(C('is_debug')){
				error("SQL querys error: $sql <br />".$this->geterror());
			}else{
				error("system error");
			}
		}else{
			return $this->result;
		}
	}
	
	/*
	 *mysqli_fetch_array
	 *BY QINIAO
	 *2010-08-29
	 *www.tuntron.com
	 */
	 
	function fetch_all_array($sql){
		$query = $this->query($sql);
		while($list_item = $this->fetch_array($query)){
			$all_array[] = $list_item;
		}
		return $all_array;
	}

	/*
	 *从结果集中取出一行作为关联数组/数字索引数组
	 */
	 
	function fetch_array($query){
		return mysqli_fetch_array($query, MYSQL_BOTH);
	}

	function once_fetch_array($sql){
		$this->result = $this->query($sql);
		return $this->fetch_array($this->result);
	}

	/*
	 *从结果集中取得一行作为数字索引数组
	 */
	 
	function fetch_row($query){
		return mysqli_fetch_row($query);
	}
	
	/*
	 *fetch_all_assoc
	 */
	 
	function fetch_all_assoc($sql,$max=0){
		$query = $this->query($sql);
        $current_index = 0;
		while($list_item = $this->fetch_assoc($query)){
			$current_index ++;
			if($current_index > $max && $max != 0){
				break;
			}
			
			$all_array[] = $list_item;
			
		}
		
		return $all_array;
	}
	
	function fetch_assoc($query){
		return mysqli_fetch_assoc($query);
	}
	
	function once_fetch_assoc($sql){
		$list 	= $this->query($sql);
		$list_array = $this->fetch_assoc($list);
		return $list_array;
	}

    function get_value($sql){
        $query	= $this->query($sql);
        $value = $this->fetch_row($query);
        return $value[0];
    }
	

	/*
	 *获取行的数目
	 */
	 
	function num_rows($query){
		return mysqli_num_rows($query);
	}
	
	function once_num_rows($sql){
		$query=$this->query($sql);
		return mysqli_num_rows($query);
	}
	
	/*
	 *获得结果集中字段的数目
	 */
	 
	function num_fields($query){
		return mysqli_num_fields($query);
	}

	/*
	 *取得上一步INSERT产生的ID
	 */
	
	function insert_id(){
		return mysqli_insert_id($this->conn);
	}
	
	/*
	 *数组添加
	 */
	 
	function insertArr($arrData,$table,$where=''){
		$Item = array();
		foreach($arrData as $key=>$data){
			$Item[] = "`$key`='".( $data)."'";
		}
		$intStr = implode(',',$Item);

		$this->query("insert into $table  SET $intStr $where");
		return mysqli_insert_id($this->conn);
	}
	
	/*
	 *数组更新(Update)
	 */

	function updateArr($arrData,$table,$where=''){
		$Item = array();
		foreach($arrData as $key => $value)
		{
			$Item[] = "`$key`='".($value)."'";
		}
		$upStr = implode(',',$Item);
		$result = $this->query("UPDATE $table  SET  $upStr $where");

        return $result;
	}
	 
	/*
	 *获取mysqli错误
	 */
	function geterror(){
		return mysqli_error($this->conn);
	}

	/*
	 *Get number of affected rows in previous mysqli operation
	 */
	 
	function affected_rows(){
		return mysqli_affected_rows($this->conn);
	}
	/*
	 *获取数据库版本信息
	 */
	 
	function getmysqlVersion(){
		return @mysqli_get_server_info($this->conn);
	}
	
	/**
	 * 对特殊字符进行过滤
	 *
	 * @param value  值
	 */
	function escape($value) {
		if(is_null($value))return 'NULL';
		if(is_bool($value))return $value ? 1 : 0;
		if(is_int($value))return (int)$value;
		if(is_float($value))return (float)$value;
		if(@get_magic_quotes_gpc())$value = stripslashes($value);
		return '\''.mysqli_real_escape_string($value, $this->conn).'\'';
	}

}