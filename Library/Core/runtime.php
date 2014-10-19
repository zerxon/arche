<?php
/**
 * 
 * @author: wallace wallaceleung@163.com
 * @date: 14-10-18
 */

define('LIBRARY_PATH', 'Library/');
defined('APP_PATH') or define('APP_PATH', 'APP/');
defined('CONTROLLER_PATH') or define('CONTROLLER_PATH', APP_PATH.'Controller/');
defined('VIEW_PATH') or define('VIEW_PATH', APP_PATH.'View/');
defined('MODEL_PATH') or define('MODEL_PATH', APP_PATH.'Model/');
defined('CONFIG_PATH') or define('CONFIG_PATH', APP_PATH.'Config/');
defined('FILTER_PATH') or define('FILTER_PATH', APP_PATH.'Filter/');
defined('CACHE_PATH') or define('CACHE_PATH', APP_PATH.'_Cache/');

if($_SERVER['HTTPS'] == 'on') {
    $protocol = 'https://';
}
else {
    $protocol = 'http://';
}

$scriptName = $_SERVER['SCRIPT_NAME'];
$phpName = end(explode('/', $scriptName));
$baseUrl = $protocol.$_SERVER['HTTP_HOST'].str_replace($phpName, '', $scriptName);

define('BASE_URL', $baseUrl);
define('PUBLIC_URL', BASE_URL.'Public');

include LIBRARY_PATH.'Core/function.php';
C(tp_include(CONFIG_PATH.'baseConfig.php'));
tp_include(LIBRARY_PATH.'Core/Router.class.php');

$router = Router::getInstance();
$router->dispatch();