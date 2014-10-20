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

    private function _ruleMatch($pathInfo)
    {
        $status = false;
        $routerConfig = tp_include(CONFIG_PATH . 'routerConfig.php');

        if($pathInfo == '')
            $pathInfo = '/';

        if (is_array($routerConfig)) {
            foreach ($routerConfig as $key => $value) {
                //判断是否在起始位置匹配,防止非法规则
                if (strpos($pathInfo, $key) === 0) {
                    //如果为首页配置项
                    if($key === '/' && !($pathInfo === '/')) {
                        continue;
                    }

                    //如果module的规则值为数组，则继续进一步解析
                    if (is_array($value)) {
                        $this->_module = $value['Controller'];
                        $pattern = $value['Pattern'];

                        //判断是否为默认action
                        if($pathInfo === $key || $pathInfo === $key.'/') {
                            $status = true;

                            if(isset($pattern['/'])) {
                                $this->_action = $pattern['/'];
                            }

                            break;
                        }

                        $actionPathInfo = str_replace($key, '', $pathInfo);

                        //循环匹配action规则
                        foreach($pattern as $k => $v) {

                            $GetArray = array(); //存放get参数的临时数组
                            $k = str_replace('/', '\/', $k);
                            if(preg_match("/^$k/", $actionPathInfo, $matches)) {

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


                                if($actionPathInfo === $matchString) {
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

                    //检查是否有action
                    $actionPathInfo = str_replace($key, '', $pathInfo);
                    if($this->_action == 'index' && !empty($actionPathInfo)) {
                        if(strpos($actionPathInfo, '/') < 1) {
                            $status = true;
                            $this->_action = substr($actionPathInfo ,1);
                        }
                        else {
                            $status = false;
                        }
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

    private function _ruleMatch_bak($pathInfo)
    {
        $uri = null;
        $rulesConfig = tp_include(CONFIG_PATH . 'routerRuleConfig.php');

        if ($pathInfo == null) {
            $uri = $rulesConfig['/'];
        } else {
            foreach ($rulesConfig as $rule => $uri) {

                /*
                $optionalRules = array();
                preg_match_all('/(?<=#)[^#]+(?=#)/', $rule, $ruleMatches);
                if(count($ruleMatches) > 0 && count($ruleMatches[0]) > 0) {
                    $optionalRules = $ruleMatches[0];
                    foreach($optionalRules as $item) {
                        $rule = str_replace($item, '', $rule);
                    }
                    $rule = str_replace('#', '', $rule);
                }
                */

                $rule = str_replace('/', '\/', $rule);
                $rule = "/$rule$/";
                if (preg_match($rule, $pathInfo, $matches)) {
                    $count = count($matches);
                    if ($count > 1) {
                        for ($i = 1; $i < $count; $i++) {
                            $uri = str_replace('$' . $i, $matches[$i], $uri);
                        }
                    }
                    break;
                }
            }
        }

        return $uri;
    }

    //根据host选择不同的base controller
    private function _loadBaseController()
    {
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
    public function _runController($module, $controllerClass, $action)
    {

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
                $controllerInstance->setController($controllerClass);

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

    public static function getInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self;
        }

        return self::$_instance;
    }

    /**
     * 路由分发器
     */
    public function dispatch()
    {
        $baseControllerClass = $this->_loadBaseController();
        import($baseControllerClass);

        //$pathInfo = substr($_SERVER['PATH_INFO'], 1, strlen($_SERVER['PATH_INFO']));
        $pathInfo = $_SERVER['PATH_INFO'];
        $this->_ruleMatch($pathInfo);

        /*
        $paths = explode('/', $pathInfo);
        if (count($paths) >= 3) {
            $module = $paths[0];
            $controllerClassPrefix = ucfirst($paths[1]);
            $action = $paths[2];

            for ($i = 3; $i < count($paths); $i++) {
                $_GET[$paths[$i]] = isset($paths[++$i]) ? $paths[$i] : null;
            }
        } else {
            error("Invalid URI, URI require at least 3 parameters");
        }
        */

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