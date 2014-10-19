<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-4-21
 * Time: 下午10:16
 */

class Router {

    private static $_instance;

    public static function getInstance() {
        if(self::$_instance == null) {
            self::$_instance = new self;
        }

        return self::$_instance;
    }

    public function dispatch() {
        $pathInfo = $_SERVER['PATH_INFO'];
        $paths = explode('/', substr($pathInfo, 1, strlen($pathInfo)));

        if(count($paths) > 0 && $pathInfo !="/")
        {
            $module = $paths[0];
            if(isset($paths[1]))
                $action = $paths[1];
            else
                $action = 'index';
        }
        else {
            $module = 'index';
            $action = 'index';
        }

        //根据host选择不同的controller
        $controllers = C('controllers');
        if(is_array($controllers) && count($controllers) > 0) {
            foreach($controllers as $controller) {
                $hostRegular = $controller['host_regular'];
                if(preg_match($hostRegular, $_SERVER['HTTP_HOST'])) {
                    $baseClass = $controller['base_class'];
                    $folderPath = $controller['folder_path'];
                    $moduleSuffix = $controller['module_suffix'];

                    import($baseClass);
                    $controllerClass = ucfirst($module).$moduleSuffix;
                    $filePath = ROOT_PATH.'/'.$folderPath.$module.'/'.$controllerClass.'.class.php';
                    break;
                }
            }
        }
        else {
            error('No controller');
        }

        if(file_exists($filePath)) {
            require $filePath;

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
            else {
                error($controllerClass.' class undefined');
            }
        }
        else {
            error($filePath.' file does not exist');
        }
    }
}