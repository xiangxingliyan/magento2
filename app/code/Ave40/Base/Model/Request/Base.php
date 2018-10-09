<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017-11-21
 * Time: 上午 09:45
 */
namespace Ave40\Base\Model\Request;

/**
 */
class Base
{
    const CONF_MDW_API_HOST = '';
    const CONF_MDW_API_USE_HTTPS = '';
    const CONF_MDW_API_USERNAME = '';
    const CONF_MDW_API_PASSWORD = '';
    
    
    protected $_data;
    
    static function getEnv($key=null, $default=null) {
        static $_envs = null;
        
        if($_envs === null) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $directory = $objectManager->get('\Magento\Framework\Filesystem\DirectoryList');
            
            $rootPath = $directory->getRoot();
            $envConfigFile = $rootPath . "/env-config.php";
            $_envs = [];
    
            if(file_exists($envConfigFile)) {
                $_envs = include $envConfigFile;
            }
        }
        
        if($key === null) {
            return $_envs;
        }
        
        return isset($_envs[$key]) ? $_envs[$key] : $default;
    }
    
    /**
     * @var \Magento\Framework\Cache\FrontendInterface
     */
    protected $_cache;
    /**
     * @var \Magento\Framework\Filesystem\DirectoryList
     */
    protected $_directory;
    /**
     * 是否使用https协议
     * @var bool
     */
    protected $_isHttps = false;
    /**
     * 请求方法, 只支持GET和POST
     * @var string
     */
    protected $_method = 'POST';
    /**
     * 接口主机地址
     * @var string
     */
    protected $_host = "";
 
    /**
     * 接口路径
     * @var string
     */
    protected $_path;
    /**
     * 接口url地址, 如果此成员的值为空则使用上面的host和path组建完整的接口路径
     * @var
     */
    protected $_url;
    /**
     * 请求操作是否执行成功
     * @var bool
     */
    protected $_ok=false;
    /**
     * curl执行请求后返回的消息
     * @var null
     */
    protected $_message = null;
    /**
     * 接口返回的响应内容
     * @var null
     */
    protected $_content = null;
    /**
     * 请求接口的http状态码
     * @var null
     */
    protected $_httpCode = null;
    /**
     * 以json的形式解析接口返回的数据, 并将解析结果储存在此成员中
     * @var null
     */
    protected $_requestResult = null;
    protected $_requestData=null;
    
    /**
     * @var \Ave40\Base\Model\Request\Base
     * 用来获取token的类, 如果没有指定则自动获取
     */
    protected $_FETCH_TOKEN_CLASS;
    /**
     * 接口无效代码
     */
    const CODE_INVALID_TOKEN = 100001;
    /**
     * 订单已经存在
     */
    const CODE_ORDER_EXISTS = 101001;
    
    public function __construct()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $cache = $objectManager->get('\Magento\Framework\App\CacheInterface')->getFrontend();
        $directory = $objectManager->get('\Magento\Framework\Filesystem\DirectoryList');
        $this->_cache = $cache;
        $this->_directory = $directory;
        $this->_construct();
    }
    
    /**
     * 设置请求的数据
     * @param null $key
     * @param null $value
     * @return $this
     */
    public function setRequestData($key=null, $value=null)
    {
        if(is_string($key)) {
            $this->_requestData[$key] = $value;
            return $this;
        }
    
        $this->_requestData = $key;
        return $this;
    }
    
    /**
     * 获取已经设置的请求的数据
     * @param null $key
     * @return mixed
     */
    public function getRequestData($key=null)
    {
        if($key === null) {
            return $this->_requestData;
        }
        
        return $this->_requestData[$key];
    }
    
    /**
     * 设置请求的主机
     * @param $host
     * @return $this
     */
    public function setHost($host)
    {
        $this->_host = preg_replace('#^[a-z0-9_]+://#i', '', trim($host));
        return $this;
    }
    
    /**
     * 获取请求的主机, 只有在url成员为空的时候有效
     * @return string
     */
    public function getHost()
    {
        return $this->_host;
    }
    
    /**
     * 设置请求的url, 如果url不为空, 则setHost和setPath都将无效
     * 也就是说此参数的值优先级高于host和path的设置
     * @param $url
     * @return $this
     */
    public function setUrl($url)
    {
        $this->_url = $url;
        return $this;
    }
    
    /**
     * 获取请求的url, 如果没有是用setUrl进行设置, 则自动拼接host和path
     * @return string
     */
    public function getUrl()
    {
        if($this->_url) {
            return $this->_url;
        }
        
        $scheme = $this->_isHttps ? 'https://' : 'http://';
        return $scheme . $this->_host . '/' . ltrim($this->_path, '/');
    }
    
    /**
     * 设置请求路径, 只有在url成员为空的时候有效
     * @param $path
     * @return $this
     */
    public function setPath($path)
    {
        $this->_path = $path;
        return $this;
    }
    
    /**
     * 获取请求路径
     * @return string
     */
    public function getPath()
    {
        return $this->_path;
    }
    
    /**
     * 使用指定方式请求, 支持POST和GET
     * @param $method
     * @return $this
     */
    public function useMethod($method)
    {
        if(empty($method)) {
            $method = "POST";
        }
        
        $this->_method = $method;
        return $this;
    }
    
    /**
     * 是用https协议请求接口, 如果use为false则使用http协议
     * @param bool $use
     * @return $this
     */
    public function useHttps($use=true)
    {
        $this->_isHttps = $use;
        return $this;
    }
    
    /**
     * 发送请求
     * @return $this
     */
    public function send()
    {
        $reqInstance = curl_init();
        
        curl_setopt($reqInstance, CURLOPT_HEADER, false);
        curl_setopt($reqInstance, CURLOPT_RETURNTRANSFER, 1);
        
        switch (strtoupper($this->_method)) {
            case "POST":
                curl_setopt($reqInstance, CURLOPT_URL, $this->getUrl());
                curl_setopt($reqInstance, CURLOPT_POST, 1);
                curl_setopt($reqInstance, CURLOPT_POSTFIELDS, $this->getRequestData());
                break;
            default:
                $url = $this->getUrl();
                $urlDetail = parse_url($url);
                parse_str($urlDetail['query'], $queryParams);
                $queryParams = array_merge((array)$queryParams, $this->getRequestData());
                $urlDetail['query'] = $queryParams;
                $userStr = implode(':', [$urlDetail['user'], $urlDetail['pass']]);
                $queryStr = http_build_query($urlDetail['query']);
                
                $newUrl = (empty($urlDetail['scheme']) ? '' : ($urlDetail['scheme'].'://'))
                    . (empty($userStr) ? '' : ($userStr . '@'))
                    . implode(':', [$urlDetail['host'], $urlDetail['port']])
                    . $urlDetail['path']
                    . (empty($queryStr) ? '' : '?' . ($queryStr));
                    
                curl_setopt($reqInstance, CURLOPT_URL, $newUrl);
        }
        
        $result = curl_exec($reqInstance);
        $this->_httpCode = curl_getinfo($reqInstance,CURLINFO_HTTP_CODE);
        $this->_message = curl_error($reqInstance);
        curl_close($reqInstance);
        
        if($result === false || $this->_httpCode != '200') {
            $this->_message .= "[url={$this->getUrl()}]";
            $this->_ok = false;
            $this->_content = $result;
            return $this;
        }
        
        $this->_ok = true;
        $this->_content = $result;
        $this->_requestResult = json_decode($result, true);
        
        return $this;
    }
    
    /**
     * 接口调用是否成功,网络层面
     * @return bool
     */
    public function ok()
    {
        return $this->_ok;
    }
    
    /**
     * 接口调用是否失败,网络层面
     * @return bool
     */
    public function fail()
    {
        return !$this->_ok;
    }
    
    /**
     * 获取接口返回的响应内容
     * @return null
     */
    public function getContent()
    {
        return $this->_content;
    }
    
    /**
     * 将接口返回的内容以json解析
     * @return null
     */
    public function getContentParseAsJson()
    {
        return $this->_requestResult;
    }
    
    /**
     * 获取整个接口返回值的数组,以json解析
     * @return null
     */
    public function getResult()
    {
        return $this->getContentParseAsJson();
    }
    
    /**
     * 获取curl链接时的错误消息
     * @return null
     */
    public function getCurlMessage()
    {
        return $this->_message;
    }
    
    /**
     * 获取curl的http状态码
     * @return null
     */
    public function getHttpCode()
    {
        return $this->_httpCode;
    }
    
    /**
     * 从接口返回值中获取message字段
     * @return string
     */
    public function getResultMessage()
    {
        return implode(';', $this->_requestResult['message']);
    }
    
    /**
     * 从接口返回值中获取success字段
     * @return mixed
     */
    public function getResultSuccess()
    {
        return $this->_requestResult['success'];
    }
    
    /**
     * 从接口返回值的success字段中判断是否是失败
     * @return bool
     */
    public function getResultFail()
    {
        return !$this->_requestResult['success'];
    }
    
    /**
     * 从接口返回值中获取code字段
     * @return mixed
     */
    public function getResultCode()
    {
        return $this->_requestResult['code'];
    }
    
    /**
     * 比较result code
     * @param $code
     * @return bool
     */
    public function ifResultCodeIs($code)
    {
        return $this->getResultCode() == $code;
    }
    
    /**
     * 比较result code
     * @param $code
     * @return bool
     */
    public function ifResultCodeIn($code)
    {
        return in_array($this->getResultCode(), (array)$code);
    }
    
    /**
     * 从接口返回值中获取data字段的值
     * @param null $key
     * @return mixed
     */
    public function getResultData($key=null)
    {
        if(null !== $key) {
            return $this->_requestResult['data'][$key];
        }
        
        return $this->_requestResult['data'];
    }
    
    public function __call($name, $arguments)
    {
        $op = strtolower(substr($name, 0, 3));
        $paramName = strtolower(str_replace('#([A-Z])#', '_$1',  lcfirst(substr($name, 3))));
        
        if($op == 'get') {
            return $this->getData($paramName);
        }
        
        if($op == 'set') {
            return $this->setData($paramName, $arguments[0]);
        }
        
        return $this;
    }
    
    public function getData($key=null) {
        if($key == null) {
            return $this->_data;
        }
        return $this->_data[$key];
    }
    
    public function setData($key, $value) {
        $this->_data[$key] = $value;
        return $this;
    }
}