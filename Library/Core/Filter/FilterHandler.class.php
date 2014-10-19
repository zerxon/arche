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

    public function filter($curController, $curAction) {
        if(is_array($this->_filterConfigs) && count($this->_filterConfigs) > 0) {
            foreach($this->_filterConfigs as $filter=>$config) {
                $controllers = $config['controllers'];
                if(is_array($controllers) && count($controllers) > 0) {
                    foreach($controllers as $controller=>$actions) {
                        if($controller === $curController) {
                            foreach($actions as $action) {
                                if($action === $curAction) {
                                    import('Filter.'.$filter);

                                    $filterInstance = new $filter();
                                    $status =  $filterInstance->doFilter();

                                    if($status == false)
                                        return false;

                                    break;
                                }
                            }
                        }
                        else {
                            continue;
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