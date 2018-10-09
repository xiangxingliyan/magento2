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
class Fetchtoken extends Base
{
    const CK_TOKEN_USER='ave40_base_erp/general/mdw_api_token_user';
    const CK_TOKEN_PASS='ave40_base_erp/general/mdw_api_token_pass';
    
    protected $_path = "/api/getToken";
    
    public function __construct()
    {
        parent::__construct();
        
        $this->setRequestData('user', self::getEnv(self::CONF_MDW_API_USERNAME));
        $this->setRequestData('pass', self::getEnv(self::CONF_MDW_API_PASSWORD));
    }
}