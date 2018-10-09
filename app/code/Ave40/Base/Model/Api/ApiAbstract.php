<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018-6-12
 * Time: 下午 05:04
 */
namespace Ave40\Base\Model\Api;

abstract class ApiAbstract {
    const CODE_FAIL =  1;
    const CODE_SUCCESS =  0;
    const SCODE_FAIL =  'FAIL';
    const SCODE_SUCCESS =  'OK';
    
    public function makeReturn($data) {
        return json_encode($data);
    }
    
    protected function _makeApiResultJson($message, $scode=self::SCODE_FAIL, $code=self::CODE_FAIL, $data=null) {
        return $this->makeReturn([
            'message' => $message,
            'scode' => $scode,
            'code' => $code,
            'data' => $data
        ]);
    }
    
    public function makeFailedReturn($message, $scode=self::SCODE_FAIL, $code=self::CODE_FAIL, $data=null) {
        return $this->_makeApiResultJson($message, $scode, $code, $data);
    }
    
    public function makeSuccessfulReturn($message='', $data=[], $scode=self::SCODE_SUCCESS, $code=self::CODE_SUCCESS) {
        return $this->_makeApiResultJson($message, $scode, $code, $data);
    }
    
    public function makeSuccessfulDataReturn($data, $message='', $scode=self::SCODE_SUCCESS, $code=self::CODE_SUCCESS) {
        return $this->_makeApiResultJson($message, $scode, $code, $data);
    }
}