<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-4-21
 * Time: 下午4:43
 */

import('Library.Core.View.Template');

class View {
    private $_var;

    public function __construct() {
        $this->_var = array();
    }

    public function assign($key, $value) {
        $this->_var[$key] = $value;
    }

    public function remove($key = null) {
        if($key != null)
            unset($this->_var[$key]);
        else
            unset($this->_var);
    }

    public function display($tplName) {
        //导出模板变量
        extract($this->_var);

        //渲染模板
        $viewConfig = C('view');
        $tplName = $tplName.$viewConfig['suffix'];
        $cacheEnable = $viewConfig['cache_enable'];
        $cacheExpiry = $viewConfig['cache_expiry'];
        $cachePath = CACHE_PATH.$viewConfig['cache_folder'].'/';

        $template = new Template();
        $cacheFile = $template->compile($tplName, VIEW_PATH, $cachePath, $cacheEnable, $cacheExpiry);

        include_once $cacheFile;
    }
}