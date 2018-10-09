<?php
namespace Ave40\Base\Model\Request\MdwApi;
/**
 * Class Ave40_Base_Model_Erp_Mdw_Request
 */
class Tokenbase extends Base
{
    protected $_tokenFetchCallback=null;
    protected $_tokenCleanCallback=null;
    
    public function _construct()
    {
        parent::_construct();
    }
    
    /**
     * @return string
     * @throws \Exception
     */
    protected function _getFetchTokenClass() {
        if($this->_FETCH_TOKEN_CLASS) {
            return $this->_FETCH_TOKEN_CLASS;
        } else {
            $class = self::class;
            $classPath = explode('\\', $class);
            array_pop($classPath);
            $namespace = implode('\\', $classPath);
            $className = $namespace . '\\Fetchtoken';
            return $className;
        }
    }
    
    /**
     * @return mixed
     * @throws \Exception
     */
    public function getToken() {
        $cache = $this->_cache;
        $token = $cache->load(self::K_MDW_TOKEN_CACHE);
    
        if(!empty($token)) {
            return $token;
        }
        
        $className = $this->_getFetchTokenClass();
        /** @var \Ave40\Base\Model\Request\MdwApi\Base $instance */
        $instance = new $className;
        $instance->send();
    
        if($instance->fail()) {
            throw new \Exception($instance->getHttpCode() . ':' . $instance->getCurlMessage());
        }
    
        if($instance->getResultFail()) {
            throw new \Exception($instance->getResultMessage());
        }
    
        $token = $instance->getResultData('token');
    
        if(empty($token)) {
            throw new \Exception('请求的token是空的:' . $instance->getContent());
        }
    
        $expire = intval($instance->getResultData('expire'));
    
        //提前半个小时过期
        $expire = $expire > 30*60 ? $expire-30*60 : $expire;
        $cache->save($token, self::K_MDW_TOKEN_CACHE, [], $expire);
        return $token;
    }

    public function cleanToken() {
        $cache = $this->_cache;
        $cache->save('', self::K_MDW_TOKEN_CACHE, [], 0);
    }
    
    public function send()
    {
        $retry = false;
    
        RETRY:
        try {
            $token = $this->getToken();
        } catch(\Exception $e) {
            $this->_message .= "获取token失败:" .  $e->getMessage();
            $this->_ok = false;
            return $this;
        }
    
        if(empty($token)) {
            $this->_message .= "获取的token为空";
            $this->_ok = false;
            return $this;
        }
    
        $this->setRequestData('token', $token);
    
        parent::send();
    
        if($this->ifResultCodeIs(self::CODE_INVALID_TOKEN) && !$retry) {
            $retry = true;
            $this->cleanToken();
            goto RETRY;
        }
    
        return $this;
    }
}