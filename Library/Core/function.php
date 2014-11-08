<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-4-20
 * Time: 下午9:25
 */

function C($name = null,$val = null) {
    static $_config = array();
    if(empty($name)) {
        return $_config;
    } elseif(is_string($name)) {
        if(empty($val)) {
            if(!strpos($name,'=>')) {
                //一维
                return isset($_config[$name]) ? $_config[$name] : null;
            } else {
                //目前只支持二维
                $name = explode('=>',$name);
                return isset($_config[$name[0]][$name[1]]) ? $_config[$name[0]][$name[1]] : null;
            }
        } else {
            if(!strpos($name,'=>')) {
                //直接设置
                $_config[$name] = $val;
            } else {
                //设置二维
                $name = explode('=>',$name);
                $_config[$name[0]][$name[1]] = $val;
            }
        }
    } elseif(is_array($name)) {
        foreach($name as $key=>$value) {
            $_config[$key] = $value;
        }
        return ;
    } else {
        throw new Exception('参数类型出错');
        return ;
    }
}

function import($class,$ext = '.class.php') {
    $pos = strpos($class,'.');
    $whosePackage = substr($class,0,$pos);
    $name = str_replace('.','/',$class);
    $name = str_replace($whosePackage.'/','',$name);

    $baseUrl = '';
    $constant = strtoupper($whosePackage).'_PATH';
    if(defined($constant)) {
        $baseUrl = constant($constant);
    }

    $path = $baseUrl.$name.$ext;

    if(file_exists($path))
        tp_include($path);
    else
        error($path.' file not found');
}

function tp_include($path = null) {
    static $_require = array();

    if(!is_file($path))
        error($path.' file does not exist');

    if(!isset($_require[$path])) {
        $_require[$path] = '';
        return require $path;
    } else {
        return true;
    }
}

/**
 * 写文件
 * @param string $file - 需要写入的文件，系统的绝对路径加文件名
 * @param string $content - 需要写入的内容
 * @param string $mod - 写入模式，默认为w
 * @param boolean $exit - 不能写入是否中断程序，默认为中断
 * @return boolean 返回是否写入成功
 */
function isWriteFile($file, $content, $mod = 'w', $exit = TRUE) {
    if(!@$fp = @fopen($file, $mod)) {
        if($exit) {
            exit('System File :<br>'.$file.'<br>Have no access to write!');
        } else {
            return false;
        }
    } else {
        @flock($fp, 2);
        @fwrite($fp, $content);
        @fclose($fp);
        return true;
    }
}

function makeDirectory($dir) {
    return is_dir($dir) or (makeDirectory(dirname($dir)) and mkdir($dir, 0777,true));
}

function slashes($str) {
    if(get_magic_quotes_gpc() == 0)
        $str = addslashes($str);

    return $str;
}

function debug($str, $exit = true) {
    header('Content-Type: text/html; charset=UTF-8');

    $backtrace = debug_backtrace();
    $file = $backtrace[0]['file'];
    $line = $backtrace[0]['line'];

    echo '<pre>';
    echo "<b>Line:</b> $line <b>In file:</b> $file<br/><br/>";
    print_r($str);

    if($exit)
        exit;
}

function error($str) {
    if(C('is_debug')) {
        header('Content-Type: text/html; charset=UTF-8');
        echo '<pre>';
        debug_print_backtrace();
        exit;
    }
    else {
        header('HTTP/1.1 404 Not Found');
    }
}

function oneTimeSession($key) {
    $session = null;

    if(isset($_SESSION[$key])) {
        $session = $_SESSION[$key];
        unset($_SESSION[$key]);
    }

    return $session;
}

function getFriendlyTime($time = null, $format = 'Y-m-d H:i:s') {
    if($time == null)
        $time = time();

    return date($format, $time);
}

function week($index) {
    $week= array('日','一','二','三','四','五','六');

    return $week[$index];
}

function microTimestamp() {
    $timestamp = microtime(true) * 10000;

    return $timestamp;
}
