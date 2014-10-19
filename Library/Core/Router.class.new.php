<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-4-21
 * Time: 下午10:16
 */

class Router {

    private static $_instance;

    private $_controllersFolderPath;
    private $_moduleSuffix;

    //根据host选择不同的base controller
    private function _switchBaseController() {
        $controllers = C('controllers');
        if(is_array($controllers) && count($controllers) > 0) {
            foreach($controllers as $controller) {
                $hostRegular = $controller['host_regular'];
                if(preg_match($hostRegular, $_SERVER['HTTP_HOST'])) {
                    $baseControllerClass = $controller['base_class'];
                    $this->_controllersFolderPath = $controller['folder_path'];
                    $this->_moduleSuffix = $controller['module_suffix'];

                    return $baseControllerClass;
                    break;
                }

            }
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
    public function _runController($controllerClass, $module, $action) {
        $status = true;

        //过滤器
        if(C('filter_enable')) {
            import('Library.Core.Filter.FilterHandler');
            $filterController = FilterHandler::getInstance();
            $status = $filterController->filter($controllerClass, $action);
        }

        if($status) {
            $controllerInstance = new $controllerClass();
            $controllerInstance->setController($controllerClass);
            $controllerInstance->setModule($module);

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
        $baseControllerClass = $this->_switchBaseController();
        import($baseControllerClass);

        $pathInfo = $_SERVER['PATH_INFO'];
        $paths = explode('/', substr($pathInfo, 1, strlen($pathInfo)));
        $pathIndex = 0;
        if(count($paths) > 0 && $pathInfo !="/")
        {
            $module = $paths[$pathIndex++];
            if(isset($paths[$pathIndex]))
                $action = $paths[$pathIndex++];
            else
                $action = 'index';
        }
        else {
            $module = 'index';
            $action = 'index';
        }

        $controllerStatus = false;
        $classPrefix = $module;
        do {
            $controllerClass = ucfirst($classPrefix).$this->_moduleSuffix;
            $filePath = ROOT_PATH.'/'.$this->_controllersFolderPath.$module.'/'.$controllerClass.'.class.php';

            if(file_exists($filePath)) {
                include_once $filePath;

                if(class_exists($controllerClass)) {
                    if(method_exists($controllerClass, $action)) {

                        $controllerStatus = true;
                        $this->_runController($controllerClass, $module, $action);
                    }
                    else {
                        $classPrefix = $action;
                        $action = isset($paths[$pathIndex]) ? $paths[$pathIndex++] : 'index';
                    }
                }
                else {
                    error($controllerClass.' class undefined');
                }
            }
        } while(!$controllerStatus && $pathIndex <= 3 && $pathIndex <= count($paths));

        if(!$controllerStatus)
            error($filePath.' file does not exist');
    }
}