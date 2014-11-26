<?php
/**
 *
 * @author: wallace wallaceleung@163.com
 * @date: 14-10-18
 */

error_reporting(E_ERROR);

define('ROOT_PATH', dirname(__FILE__));
define('APP_PATH', 'API/');

//API Constant
define('MODEL_PATH', 'APP/Model/');

define('SITE_URL', 'http://172.18.169.9/arche/web.php/');
define('SESSION_USER', 'session_user');

require_once 'Library/Core/runtime.php';