<?php
/**
 * 
 * @author: wallace wallaceleung@163.com
 * @date: 14-5-27
 */

class Service {
    private static $_instance;

    public static function getInstance() {
        if(self::$_instance == null) {
            self::$_instance = new self;
        }

        return self::$_instance;
    }
}