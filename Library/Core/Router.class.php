<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-4-21
 * Time: 下午10:16
 */

class Router
{

    private static $_instance;

    private $_module = 'Index';
    private $_action = 'index';

    private $_controllerClassSuffix;

    private function _ruleMatch($pathInfo) {

        $status = false;
        $routerConfig = tp_include(CONFIG_PATH . 'routerConfig.php');

        if($pathInfo == '')
            $pathInfo = '/';

        if (is_array($routerConfig)) {
            foreach ($routerConfig as $moduleKey => $value) {

                $noRegModuleKey = str_replace('\\', '', $moduleKey);
                $pregModuleKey = str_replace('/', '\/', $moduleKey);

                //如果为首页配置项
                if($moduleKey === '/') {
                    if($pathInfo === '/') {
                        $this->_module = $value;
                        $status = true;
                        break;
                    }
                    else {
                        continue;
                    }
                }
                //匹配module规则，为正则表达式
                else if(preg_match("/^$pregModuleKey/", $pathInfo)) {
                    //如果module的规则值为数组，则继续进一步解析action
                    if (is_array($value)) {
                        $this->_module = $value['Controller'];
                        $actionPattern = $value['Pattern'];

                        //判断规则是否匹配默认action
                        if($pathInfo === $noRegModuleKey || $pathInfo === $noRegModuleKey.'/') {
                            $status = true;

                            if(isset($actionPattern['/'])) {
                                $this->_action = $actionPattern['/'];
                            }

                            break;
                        }

                        $actionPathInfo = preg_replace("/^$pregModuleKey/", '', $pathInfo);

                        //循环匹配action规则
                        foreach($actionPattern as $actionKey => $v) {
                            $GetArray = array(); //存放get参数的临时数组

                            //默认action之前已作处理，在此处跳过
                            if($actionKey === '/') {
                                continue;
                            }

                            $pregActionKey = str_replace('/', '\/', $actionKey);
                            if(preg_match("/^$pregActionKey/", $actionPathInfo, $matches)) {

                                $matchString = array_shift($matches);

                                //匹配action规则的参数值
                                if(strpos($v, '?') > 0 && preg_match_all('/[\?|&]([^=]+=[^&]+)/i', $v, $paramsMath)) {
                                    $s = implode('', $paramsMath[0]);
                                    $v = str_replace($s, '' ,$v);

                                    foreach($paramsMath[1] as $index => $item) {
                                        //将参数索引替换成具体的参数值
                                        $item = str_replace('$'.($index+1), $matches[$index], $item);

                                        //划分参数键值，将其加入到$_GET里面
                                        $paramArr = explode('=', $item);
                                        $paramKey = $paramArr[0];
                                        $paramValue = $paramArr[1];
                                        $GetArray[$paramKey] = $paramValue;
                                    }

                                }

                                if($actionPathInfo == $matchString) {
                                    $status = true;

                                    $this->_action = $v;

                                    //将get参数临时数组加入到$_GET
                                    if(!empty($GetArray)) {
                                        foreach($GetArray as $getKey => $getValue) {
                                            $_GET[$getKey] = $getValue;
                                        }
                                    }

                                    break;
                                }

                            }

                        }
                    }
                    else {
                        $this->_module = $value;
                        $status = true;
                    }
                }
                else {
                    continue;
                }

                //检查是否有action
                if($this->_action == 'index') {
                    $actionPathInfo = preg_replace("/^$pregModuleKey/", '', $pathInfo);

                    if(strlen(trim($actionPathInfo)) > 0) {

                        if(strpos($actionPathInfo, '/') < 1) {
                            $status = true;
                            $this->_action = substr($actionPathInfo ,1);
                            break;
                        }
                        else {
                            $status = false;
                        }
                    }
                    else {
                        break;
                    }
                }

            }
        } else {
            error('routerConfig is not array');
        }

        if(!$status)
            error('Invalid pathInfo: '.$pathInfo.', It can not match any available router pattern');

        /*
        debug($this->_module, false);
        debug($this->_action, false);
        debug($_GET);
        */
    }

    //根据host选择不同的base controller
    private function _loadBaseController() {
        $controllerConfig = C('controller');
        if (is_array($controllerConfig)) {
            $baseControllerClass = $controllerConfig['base_class'];
            $this->_controllerClassSuffix = $controllerConfig['class_suffix'];

            return $baseControllerClass;
        } else {
            error('No controller');
        }
    }

    /**
     *  运行相应的控制器类
     *
     * @param $controllerClass
     * @param $module
     * @param $action
     */
    public function _runController($module, $controllerClass, $action) {

        if (class_exists($controllerClass)) {
            $status = true;

            //过滤器
            if (C('filter_enable')) {
                import('Library.Core.Filter.FilterHandler');
                $filterController = FilterHandler::getInstance();
                $status = $filterController->filter($controllerClass, $action);
            }

            if ($status) {
                $controllerInstance = new $controllerClass();
                $controllerInstance->setModule($module);

                if (method_exists($controllerInstance, $action)) {
                    $controllerInstance->setAction($action);
                    $controllerInstance->doActionStart();
                    $controllerInstance->$action();
                    $controllerInstance->doActionFinish();
                } else {
                    error($action . ' method undefined');
                }
            } else {
                error('filter can not pass');
            }
        } else {
            error($controllerClass . ' class undefined');
        }

    }

    public static function getInstance() {
        if (self::$_instance == null) {
            self::$_instance = new self;
        }

        return self::$_instance;
    }

    /**
     * 路由分发器
     */
    public function dispatch() {
        $baseControllerClass = $this->_loadBaseController();
        import($baseControllerClass);

        $pathInfo = $_SERVER['PATH_INFO'];
        $this->_ruleMatch($pathInfo);

        $controllerClassPrefix = end(explode('/', $this->_module));
        $controllerClass = $controllerClassPrefix . $this->_controllerClassSuffix;

        $filePath = CONTROLLER_PATH.$this->_module.$this->_controllerClassSuffix.'.class.php';

        if (file_exists($filePath)) {
            require $filePath;

            $this->_runController($this->_module, $controllerClass, $this->_action);
        } else {
            error($filePath . ' file does not exist');
        }

    }
}