<?php
/**
 * 
 * @author: wallace wallaceleung@163.com
 * @date: 14-11-8
 */

import('Model.entity.Schedule');

class ScheduleService {

    private $_scheduleORM;

    private static $_instance;

    public static function getInstance() {
        if(self::$_instance == null) {
            self::$_instance = new self;
        }

        return self::$_instance;
    }

    private function __construct() {
        $this->$_scheduleORM = new ORM('Schedule');
    }

}