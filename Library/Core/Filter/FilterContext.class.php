<?php
/**
 * 
 * @author: wallace wallaceleung@163.com
 * @date: 14-10-25
 */

class FilterContext {
    public $module;
    public $action;
    public $url;

    public function __construct($module, $action, $url) {
        $this->module = $module;
        $this->action = $action;
        $this->url = $url;
    }
}