<?php
/**
 * 
 * @author: wallace wallaceleung@163.com
 * @date: 14-5-10
 */

import('Library.Core.Controller.Controller');

abstract class APIController extends Controller {

    /* 格式
    {
        success:true|false,
        message:"Reasons in human readable for why success was false",
        error:"access_forbidden | invalid_params | service_unavailable",
        data:{...}
    }
     */

    protected $_ok = true;

    protected $_success = false;
    protected $_message;
    protected $_error;
    protected $_data;

    public function __construct()
    {
    }

    public function index() {
        $this->_ok = false;
        echo 'API not found';
    }

    protected function _show() {
        $apiArray = array(
            'success'=>$this->_success,
            'data'=>$this->_data
        );

        if(!$this->_success) {
            unset($apiArray['data']);
            $apiArray['error'] = $this->_error;
        }

        if ($this->_message) {
            $apiArray['message'] = $this->_message;
        }

        $formatType = C('api_format');
        $this->_output($apiArray, $formatType);
    }

    public function doActionFinish() {
        if($this->_ok)
            $this->_show();
    }

}

class API_ERROR_TYPE {
    const ACCESS_FORBIDDEN = 'access_forbidden';
    const INVALID_PARAMS = 'invalid_params';
    const SERVICE_UNAVAILABLE = 'service_unavailable';
}