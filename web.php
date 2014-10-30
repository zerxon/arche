<?php
/**
 *
 * @author: wallace wallaceleung@163.com
 * @date: 14-10-18
 */

error_reporting(E_ERROR);

define('ROOT_PATH', dirname(__FILE__));
define('APP_PATH', 'APP/');

//APP Constant
define('SITE_URL', 'http://127.0.0.1/arche/web.php/');
define('SESSION_ADMIN', 'session_admin');
define('SESSION_USER', 'session_user');

define('TIPS', 'tips');
define('TIPS_TYPE', 'tips_type');

require_once 'Library/Core/runtime.php';