<?php
/**
 *
 * @author: wallace wallaceleung@163.com
 * @date: 14-5-18
 */

class FilterHandler {
    private static $_instance;
    private $_filterConfigs;

    public function __construct() {
        $this->_filterConfigs = include CONFIG_PATH.'filterConfig.php';
    }

    public static function getInstance() {
        if(self::$_instance == null) {
            self::$_instance = new self;
        }

        return self::$_instance;
    }

    public function filter($module, $action) {
        $pathInfo = $_SERVER['PATH_INFO'];
        $uri = $pathInfo.end(explode($pathInfo, $_SERVER['REQUEST_URI']));

        if(is_array($this->_filterConfigs) && count($this->_filterConfigs) > 0) {
            foreach($this->_filterConfigs as $filter=>$config) {
                if($config['Enable']) {
                    $path = str_replace('/', '\/', $config['Path']);
                    if(preg_match("/$path/", $uri)) {
                        $type = $config['Type'];
                        $options = array();
                        $status = false;

                        if(is_array($config['Option']))
                            $options = $config['Option'];
                        else
                            array_push($options, $config['Option']);

                        //根据类型进行处理
                        if($type == FilterType::ALL) {
                            $status = true;
                        }
                        elseif($type == FilterType::CONTAIN) {
                            foreach($options as $option) {
                                $option = str_replace('/', '\/', $option);
                                if(preg_match("/$option/", $uri)) {
                                    $status = true;
                                    break;
                                }
                            }
                        }
                        elseif($type == FilterType::EXCEPT) {
                            $status = true;
                            foreach($options as $option) {
                                $option = str_replace('/', '\/', $option);
                                if(preg_match("/$option/", $uri)) {
                                    $status = false;
                                    break;
                                }
                            }
                        }

                        //如果有匹配，则执行过滤操作
                        if($status) {
                            import('Filter.'.$filter);

                            $context = new FilterContext($module, $action, $_SERVER['REQUEST_URI']);

                            $filterInstance = new $filter();
                            $result =  $filterInstance->doFilter($context);

                            return $result;
                        }

                    }
                }
                else {
                    continue;
                }
            }
        }

        return true;
    }

}

class FilterType {
    const CONTAIN = 0;
    const EXCEPT = 1;
    const ALL = 2;
}