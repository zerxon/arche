<?php
/**
 * 
 * @author: wallace wallaceleung@163.com
 * @date: 14-10-23
 */

/*
 * 服务器转回navTabId 可以把那个navTab标记为reloadFlag=1,  下次切换到那个navTab时会
 * callbackType如果是closeCurrent就会关闭当前tab
 * 只有callbackType="forward"时需要forwardUrl 值
 * navTabAjaxDone这个回调函数基本可以通用了，如果还有特殊需要也可以自定义回调函数.
 */

class DWZJson {
    public $statusCode; //ok:200, error:300, timeout:301
    public $message;
    public $navTabId;
    public $rel;
    public $callbackType;
    public $forwardUrl;

    public function  __construct() {
        $this->statusCode = 300;
        $this->message = "操作失败";
    }
}

class DWZStatusCode {
    const SUCCESS = 200;
    const ERROR = 300;
    const TIMEOUT = 301;
}

class DWZCallBackType {
    const CLOSE_CURRENT = 'closeCurrent';
    const FORWARD = 'forward';
}