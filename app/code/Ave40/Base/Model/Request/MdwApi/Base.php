<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017-11-21
 * Time: 上午 09:45
 */
namespace Ave40\Base\Model\Request\MdwApi;

/**
 * Class Ave40_Base_Model_Erp_Mdw_Request
 */
class Base extends \Ave40\Base\Model\Request\Base
{
    const CONF_MDW_API_HOST = 'MDW_API_HOST';
    const CONF_MDW_API_USE_HTTPS = 'MDW_API_USE_HTTPS';
    const CONF_MDW_API_USERNAME = 'MDW_API_USERNAME';
    const CONF_MDW_API_PASSWORD = 'MDW_API_PASSWORD';
    
    const K_MDW_TOKEN_CACHE = 'AVE40_MDW_TOKEN_CACHE';
    
    /**
     * 构造
     */
    public function _construct()
    {
        $host = self::getEnv(self::CONF_MDW_API_HOST);
        
        if($host) {
            $this->setHost($host);
        }
        
        if(self::getEnv(self::CONF_MDW_API_USE_HTTPS)) {
            $this->useHttps(true);
        }
    }
}