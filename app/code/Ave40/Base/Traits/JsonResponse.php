<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018-08-28 0028
 * Time: 上午 11:59
 */

namespace Ave40\Base\Traits;

Trait JsonResponse {
    public function respondJson($error, $scode, $message, $data) {
        header('Content-type: application/json');
        echo json_encode([
            'error' => $error,
            'scode' => $scode,
            'success' => !$error,
            'message' => $message,
            'data' => $data
        ]);
        exit;
    }
    
    public function respondJsonSuccess($message="OK", $data=[], $scode='OK') {
        $this->respondJson(0, $scode, $message, $data);
    }
    
    public function respondJsonSuccessData($data=[], $message="OK", $scode="OK") {
        $this->respondJson(0, $scode, $message, $data);
    }
    
    public function respondJsonFail($message='FAIL', $scode="FAIL", $data=[]) {
        $this->respondJson(1, $scode, $message, $data);
    }
}