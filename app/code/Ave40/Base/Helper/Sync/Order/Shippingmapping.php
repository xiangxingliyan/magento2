<?php

namespace Ave40\Base\Helper\Sync\Order;

use Magento\Framework\App\Helper\Context;

class Shippingmapping extends \Magento\Framework\App\Helper\AbstractHelper
{
    
    const SHIPPINGMAPPING_COUNTRIES_CACHE_KEY = 'AVE40_SHIPPINGMAPPING_COUNTRIES';
    
    protected $_shipconfig;
    protected $_obm;
    protected $_countryCollectionFactory;
    protected $_cache;
    
    protected $_shippingMethods;
    protected $_mappings=null;
    protected $_mgMethods = null;
    protected $_countryList = null;
    
    
    /** @var \Ave40\Base\Model\ShippingmappingFactory  */
    protected $_shippingmappingModelFactory;
    /** @var \Ave40\Base\Model\ResourceModel\Shippingmapping\CollectionFactory  */
    protected $_shippingmappingCollectionFactory;
    /** @var \Ave40\Base\Model\ResourceModel\Shippingmapping  */
    protected $_resourceModel;
    
    public function __construct(
        Context $context,
        \Magento\Shipping\Model\Config $shipconfig,
        \Magento\Directory\Model\ResourceModel\Country\CollectionFactory  $countryCollectionFactory,
        \Magento\Framework\App\CacheInterface $cache,
        \Ave40\Base\Model\ShippingmappingFactory $shippingmappingModelFactory,
        \Ave40\Base\Model\ResourceModel\Shippingmapping $shippingmappingResource,
        \Ave40\Base\Model\ResourceModel\Shippingmapping\CollectionFactory $shippingmappingCollectionFactory
    )
    {
        parent::__construct($context);
        $this->_shipconfig = $shipconfig;
        $this->_obm = \Magento\Framework\App\ObjectManager::getInstance();
        $this->_countryCollectionFactory = $countryCollectionFactory;
        $this->_cache = $cache;
        $this->_shippingmappingModelFactory = $shippingmappingModelFactory;
        $this->_resourceModel = $shippingmappingResource;
        $this->_shippingmappingCollectionFactory = $shippingmappingCollectionFactory;
    }
    
    public function getShippingMethodList()
    {
        // $methods = Mage::getSingleton('shipping/config')->getActiveCarriers();
        $methods = $this->_shipconfig->getActiveCarriers();
        $shipMethodAry = [];
        
        foreach ($methods as $shippingCode => $shippingModel)
        {
            $shippingTitle = $this->scopeConfig->getValue('carriers/'.$shippingCode.'/title');
            
            $shipMethod = [
                'code' => $shippingCode,
                'title' => $shippingTitle
            ];
    
            $shipMethodAry []= $shipMethod;
        }
        
        return $shipMethodAry;
    }
    
    public function getAvailableShippingMethods()
    {
        if(!$this->_shippingMethods) {
            $methods = $this->_shipconfig->getActiveCarriers();
            $this->_shippingMethods = [];
            
            foreach($methods as $key => $methodType) {
                foreach($methodType->getAllowedMethods() as $methodKey => $methodName) {
                    $this->_shippingMethods[$key. '_' . $methodKey] = $methodName ;
                }
            }
            
        }
        
        return $this->_shippingMethods;
    }
    
    public function getAllMappings() {
        if($this->_mappings) {
            return $this->_mappings;
        }
        /**
         * @var $collection \Ave40\Base\Model\ResourceModel\Shippingmapping\Collection
         */
        $collection = $this->_obm->create(\Ave40\Base\Model\Shippingmapping::class)->getCollection();
        // $collection = Mage::getModel('ave40_base/erp_shippingmapping')->getCollection();
        $collection->addFieldToSelect('*');
        $collection->addOrder('shipping_code', 'ASC');
        $collection->addOrder('country', 'DESC');
        $collection->addOrder('battery', 'DESC');
        $collection->load();
        
        return $this->_mappings = $collection->getData();
    }
    
    public function getAllMgMethods() {
        if($this->_mgMethods) {
            return $this->_mgMethods;
        }
        
        $methods = $this->_shipconfig->getAllCarriers();
        $mgMethods = [];
        
        foreach($methods as $key => $methodType) {
            foreach($methodType->getAllowedMethods() as $methodKey => $methodName) {
                $mgMethods[$key. '_' . $methodKey] = $methodName;
            }
        }
        
        return $this->_mgMethods = $mgMethods;
    }
    
    public function getMethodName($mgMethodKey) {
        $methods = $this->getAllMgMethods();
        return $methods[$mgMethodKey];
    }
    
    public function getCountryList() {
        if($this->_countryList) {
            return $this->_countryList;
        }
        
        $cache = $this->_cache->getFrontend();
        // $cache = Mage::getModel('ave40_widget/partialcache');
        
        if($list = $cache->load(self::SHIPPINGMAPPING_COUNTRIES_CACHE_KEY)) {
            $list = json_decode($list, true);
            if(!empty($list)) {
                return $list;
            }
        }
        
        // $list = Mage::getModel('directory/country_api')->items();
        $newList = [];
        
        foreach($this->_getCountries() as $row) {
            if(!$row['value']) {
                continue;
            }
            
            $newKey = $row['value'];
            $row['name'] = $row['label'];
            $row['country_id'] = $row['value'];
            $newList[$newKey] = $row;
        }
        
        $cache->save(json_encode($newList), self::SHIPPINGMAPPING_COUNTRIES_CACHE_KEY, []);
        return $this->_countryList = $newList;
    }
    
    public function getErpShippingData() {
        static $data;
        
        if($data) {
            return $data;
        }
        
        return $data = json_decode('{"BDT01":{"type":"八达通 ","name":"八达通 - 欧电宝PG"},"BDT02":{"type":"八达通 ","name":"八达通 - 深圳EUB"},"BDT03":{"type":"八达通 ","name":"八达通 - 华中EUB"},"BDT04":{"type":"八达通 ","name":"八达通 - 美国专线"},"BDT05":{"type":"八达通 ","name":"八达通 - 比利时专线"},"BDT06":{"type":"八达通 ","name":"八达通 - 欧邮宝香港件"},"BDT07":{"type":"八达通 ","name":"八达通 - 其他"},"BDT08":{"type":"八达通 ","name":"八达通 - 荷兰专线"},"BDT09":{"type":"八达通 ","name":"八达通 - 通邮宝PG"},"BDT10":{"type":"八达通 ","name":"八达通 - 欧邮宝PG"},"BDT11":{"type":"八达通 ","name":"八达通 - 深圳挂号"},"BDT12":{"type":"八达通 ","name":"八达通 - 俄特快专线"},"DB01":{"type":"鼎博","name":"鼎博 - 俄罗斯专线"},"DSF01":{"type":"递四方","name":"递四方 - 新加坡小包"},"HG01":{"type":"汉高","name":"汉高 - HK EMS"},"HQ01":{"type":"海强","name":"海强 - DHL"},"HQT01":{"type":"环球通","name":"环球通 - DHL到付"},"HST01":{"type":"豪速通","name":"豪速通 - DHL"},"HST02":{"type":"豪速通","name":"豪速通 - UPS"},"HST03":{"type":"豪速通","name":"豪速通 - TNT"},"HST04":{"type":"豪速通","name":"豪速通 - FEDEX"},"HST05":{"type":"豪速通","name":"豪速通 - EMS"},"HST06":{"type":"豪速通","name":"豪速通 - ARAMEX"},"HST07":{"type":"豪速通","name":"豪速通 - 欧洲专线电子烟"},"HST08":{"type":"豪速通","name":"豪速通 - 日本专线电子烟"},"HST09":{"type":"豪速通","name":"豪速通 - 越南专线"},"HST10":{"type":"豪速通","name":"豪速通 - 香港专线"},"HST11":{"type":"豪速通","name":"豪速通 - 纯电池渠道"},"HST12":{"type":"豪速通","name":"豪速通 - 其他"},"HST13":{"type":"豪速通","name":"豪速通 - 空运"},"HST14":{"type":"豪速通","name":"豪速通 - 海运"},"JMS01":{"type":"捷买送","name":"捷买送 - 荷兰邮政小包（挂号）"},"JMS02":{"type":"捷买送","name":"捷买送 - 荷兰邮政小包（平邮）"},"JMY01":{"type":"加运美","name":"加运美"},"JR03":{"type":"久荣","name":"久荣 - 其他"},"KJ01":{"type":"快捷快递","name":"快捷快递"},"OJ01":{"type":"奥捷","name":"奥捷 - DHL"},"OJ02":{"type":"奥捷","name":"奥捷 - UPS"},"OJ03":{"type":"奥捷","name":"奥捷 - TNT"},"OJ04":{"type":"奥捷","name":"奥捷 - FEDEX"},"OJ05":{"type":"奥捷","name":"奥捷 - 其他"},"QT":{"type":"其他","name":"其他"},"RXD01":{"type":"荣迅达","name":"荣迅达 - DHL"},"RXD02":{"type":"荣迅达","name":"荣迅达 - UPS"},"RXD03":{"type":"荣迅达","name":"荣迅达 - 其他"},"SF01":{"type":"十方","name":"十方 - DHL"},"SF02":{"type":"十方","name":"十方 - UPS"},"SF03":{"type":"十方","name":"十方 - 其他"},"SFK01":{"type":"顺丰","name":"顺丰"},"SLT02":{"type":"三联通","name":"三联通 - UPS"},"SLT05":{"type":"三联通","name":"三联通 - 其他"},"SYD01":{"type":"瞬移达","name":"瞬移达 - 乌克兰专线"},"TY01":{"type":"拓宇物流","name":"拓宇物流 - UPS"},"TY02":{"type":"拓宇物流","name":"拓宇物流 - 其他"},"UPS01":{"type":"UPS官方","name":"UPS到付"},"XY01":{"type":"秀驿","name":"秀驿 - DHL"},"XY02":{"type":"秀驿","name":"秀驿 - FEDEX"},"XY03":{"type":"秀驿","name":"秀驿 - 香港专线"},"XY04":{"type":"秀驿","name":"秀驿 - UPS"},"YB01":{"type":"亦邦","name":"亦邦 - 迪拜专线"},"YS01":{"type":"优速","name":"优速"},"YT01":{"type":"圆通","name":"圆通"},"ZD01":{"type":"其他","name":"指定货代自提"},"ZM01":{"type":"其他","name":"中马物流 - 马来专线"}}', true);
    }
    
    public function getErpShippingName($erpCode) {
        $data = $this->getErpShippingData();
        return $data[$erpCode]['name'];
    }
    
    
    protected function _getCountryCollection()
    {
        $collection = $this->_countryCollectionFactory->create()->loadByStore();
        return $collection;
    }
    
    /**
     * Retrieve list of top destinations countries
     *
     * @return array
     */
    protected function _getTopDestinations()
    {
        $destinations = (string)$this->scopeConfig->getValue(
            'general/country/destinations',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return !empty($destinations) ? explode(',', $destinations) : [];
    }
    
    /**
     * Retrieve list of countries in array option
     *
     * @return array
     */
    protected function _getCountries()
    {
        return $options = $this->_getCountryCollection()
            ->setForegroundCountries($this->_getTopDestinations())
            ->toOptionArray();
    }
    
    /**
     * 查找匹配的物流方式
     * @param string $mgShippingCode magento的shipping code
     * @param string $country 国家代码
     * @param int $battery 是否带电
     * @return null|string erp物流编码
     */
    public function findShipping($mgShippingCode, $country='', $battery=0) {
        /** @var \Ave40\Base\Model\ResourceModel\Shippingmapping\Collection $collection */
        $collection = $this->_shippingmappingCollectionFactory->create();
        $collection->addFieldToFilter('shipping_code', $mgShippingCode);
        $collection->addFieldToFilter('country',[ 'in' => ['', $country]]);
        $collection->addFieldToFilter('battery',[ 'in' => [0, (int)$battery]]);
        $collection->setOrder('country', 'desc');
        $collection->setOrder('battery', 'desc');
        $collection->getSelect()->limit(1);
        $collection->load();
        
        if($collection->getSize()==0) {
            if($this->getErpShippingName($mgShippingCode)) {
                return $mgShippingCode;
            } else {
                return null;
            }
        } else {
            /** @var \Ave40\Base\Model\Shippingmapping $item */
            $item = $collection->getFirstItem();
            return $item->getErpCode();
        }
    }
}