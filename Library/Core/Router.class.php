<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-4-21
 * Time: 下午10:16
 */

class Router {

    private static $_instance;

    private $_controllerClassSuffix;

    private function _ruleMatch($pathInfo) {
        $uri = null;
        $rulesConfig = tp_include(CONFIG_PATH.'routerRuleConfig.php');

        if($pathInfo == null) {
            $uri = $rulesConfig['/'];
        }
        else {
            foreach($rulesConfig as $rule=>$uri) {

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
                if(preg_match($rule, $pathInfo, $matches)) {
                    $count = count($matches);
                    if($count > 1) {
                        for($i = 1; $i < $count; $i++) {
                            $uri = str_replace('$'.$i, $matches[$i], $uri);
                        }
                    }
                    break;
                }
            }
        }

        return $uri;
    }

    //根据host选择不同的base controller
    private function _loadBaseController() {
        $controllerConfig = C('controller');
        if(is_array($controllerConfig)) {
            $baseControllerClass = $controllerConfig['base_class'];
            $this->_controllerClassSuffix = $controllerConfig['class_suffix'];

            return $baseControllerClass;
        }
        else {
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

        if(class_exists($controllerClass)) {
            $status = true;

            //过滤器
            if(C('filter_enable')) {
                import('Library.Core.Filter.FilterHandler');
                $filterController = FilterHandler::getInstance();
                $status = $filterController->filter($controllerClass, $action);
            }

            if($status) {
                $controllerInstance = new $controllerClass();
                $controllerInstance->setModule($module);
                $controllerInstance->setController($controllerClass);

                if(method_exists($controllerInstance, $action)) {
                    $controllerInstance->setAction($action);
                    $controllerInstance->doActionStart();
                    $controllerInstance->$action();
                    $controllerInstance->doActionFinish();
                }
                else {
                    error($action.' method undefined');
                }
            }
            else {
                error('filter can not pass');
            }
        }
        else {
            error($controllerClass.' class undefined');
        }

    }

    public static function getInstance() {
        if(self::$_instance == null) {
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

        $pathInfo = substr($_SERVER['PATH_INFO'], 1, strlen($_SERVER['PATH_INFO']));
        if(C('router_enable') == true) {
            $pathInfo = $this->_ruleMatch($pathInfo);
        }
        $paths = explode('/', $pathInfo);
        if(count($paths) >= 3)
        {
            $module = $paths[0];
            $controllerClassPrefix = ucfirst($paths[1]);
            $action = $paths[2];

            for($i = 3; $i < count($paths); $i++) {
                $_GET[$paths[$i]] = isset($paths[++$i]) ? $paths[$i] : null;
            }
        }
        else {
            error("Invalid URI, URI require at least 3 parameters");
        }

        $controllerClass = $controllerClassPrefix.$this->_controllerClassSuffix;
        $filePath = CONTROLLER_PATH.$module.'/'.$controllerClass.'.class.php';

        if(file_exists($filePath)) {
            require $filePath;

            $this->_runController($module, $controllerClass, $action);
        }
        else {
            error($filePath.' file does not exist');
        }

    }
}