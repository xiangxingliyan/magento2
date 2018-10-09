<?php
/**
 * Copyright © 2017 Ihor Vansach (ihor@magefan.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Ave40\Base\Helper;


/**
 * Magefan Blog Helper
 */
class Json
{
    const HEADER_TEXT_JSON = 'Content-Type:text/json';
    const HEADER_APPLICATION_JSON = 'Content-Type:application/json';
    const HEADER_TEXT_PLAIN  = 'Content-Type:text/plain';
    const HEADER_TEXT_HTML  = 'Content-Type:text/html';
    const HEADER_NONE  = '';
    
    
    protected $_httpHeader;
    
    public function __construct($header = self::HEADER_APPLICATION_JSON) {
        $this->setHeader($header);
    }
    
    /**
     * 设置http头部
     * @param string $header
     */
    public function setHeader($header = self::HEADER_APPLICATION_JSON) {
        if($header ==self::HEADER_NONE ) {
            $this->_httpHeader = '';
        } else {
            $this->_httpHeader .= $header;
        }
    }
    
    /**
     * 返回json
     * @param $error
     * @param string $scode
     * @param $message
     * @param string $data
     */
    public function returnJsonFormat($error, $scode='', $message, $data = '') {
        $this->returnJson(array('error' => $error, 'success' => !$error, 'scode'=>$scode, 'message'=>$message, 'data' => $data));
    }
    
    public function returnJson($data = '') {
        if($this->_httpHeader) {
            header($this->_httpHeader);
        }
        
        exit(json_encode($data));
    }
    
    /**
     * 返回成功数据
     * @param $data
     * @param string $message
     * @param string $scode
     */
    public function returnSuccess($data=null, $message='', $scode='SUCCESS') {
        $this->returnJsonFormat(0, $scode, $message, $data);
    }
    
    /**
     * 返回成功提示
     * @param string $successMsg
     * @param string $scode
     */
    public function returnSuccessMessage($successMsg = '', $scode='SUCCESS') {
        $this->returnJsonFormat(0, $scode, $successMsg, '');
    }
    
    /**
     * 返回成功字符代码
     * @param string $scode
     */
    public function returnSuccessSCode($scode='SUCCESS') {
        $this->returnJsonFormat(0, $scode, '', '');
    }
    
    /**
     * 返回自定义错误
     * @param $error
     * @param string $message
     * @param string $scode
     */
    public function returnFail($error, $message='', $scode='FAIL', $data='') {
        $this->returnJsonFormat($error, $scode, $message, $data);
    }
    
    /**
     * 返回错误消息
     * @param string $message
     * @param string $scode
     */
    public function returnFailMessage($message='', $scode='FAIL', $data='') {
        $this->returnFail(1, $message, $scode, $data);
    }
    
    /**
     * 返回错误消息
     * @param string $message
     * @param string $data
     * @param string $scode
     */
    public function returnFailMessageWithData($message='', $data='', $scode='FAIL') {
        $this->returnFail(1, $message, $scode, $data);
    }
    
    /**
     * 只返回错误代码
     * @param string $scode
     */
    public function returnFailSCode($scode='FAIL') {
        $this->returnFail(1, '', $scode);
    }
}
