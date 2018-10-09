<?php
/**
 * ESPL_Autocomplete extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Elitech Rest API License
 * that is bundled with this package in the file license.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.elitechsystems.com/license.txt
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@elitechsystems.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * versions in the future. If you wish to customize this extension for your
 * needs please refer to http://www.elitechsystems.com for more information.
 *
 * @category   Elitech
 * @package    ESPL_Autocomplete
 * @author-email  info@elitechsystems.com
 * @copyright  Copyright 2017 � elitechsystems.com. All Rights Reserved
 */

namespace ESPL\Autocomplete\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_PATH_ENABLE = 'espl_autocomplete/general/enable_in_frontend';
    
    const XML_PATH_LIMIT = 'espl_autocomplete/general/limit';

    const XML_PATH_CATELIMIT = 'espl_autocomplete/general/catlimit';

    const XML_PATH_MINLENGTH = 'espl_autocomplete/general/min_length';

    const XML_PATH_CACHETIME = 'espl_autocomplete/general/cache_lifetime';

    const XML_PATH_LOCALSTORAGE = 'espl_autocomplete/general/use_local_storage';

    const XML_PATH_SHOWPRODUCTS = 'espl_autocomplete/general/showproduct';

    const XML_PATH_SHOWCATEGORY = 'espl_autocomplete/general/showcategoty';

    const XML_PATH_CATHEDTEXT = 'espl_autocomplete/design/cat_header_text';

    const XML_PATH_CATHEDBACK = 'espl_autocomplete/design/cat_header_background';

    const XML_PATH_CATRESTEXT = 'espl_autocomplete/design/cat_result_text';

    const XML_PATH_CATRESBACK = 'espl_autocomplete/design/cat_result_background';

    const XML_PATH_CATRESTEXTHOV = 'espl_autocomplete/design/cat_result_text_hover';

    const XML_PATH_PROHEDTEXT = 'espl_autocomplete/design/pro_header_text';

    const XML_PATH_PROHEDBACK = 'espl_autocomplete/design/pro_header_background';

    const XML_PATH_PRORESTEXT = 'espl_autocomplete/design/pro_result_text';

    const XML_PATH_PRORESBACK = 'espl_autocomplete/design/pro_result_background';

    const XML_PATH_PRORESTEXTHOV = 'espl_autocomplete/design/pro_result_text_hover';

    const XML_PATH_CATHEADERTEXT = 'espl_autocomplete/design/cat_header_textfont';

    const XML_PATH_PROHEADERTEXT = 'espl_autocomplete/design/pro_header_textfont';

    const XML_PATH_PRORESPRICETEXT = 'espl_autocomplete/design/pro_result_price_color';
    
    const XML_PATH_PRORESPRICETEXTHOV = 'espl_autocomplete/design/pro_result_price_color_hover';

    const XML_PATH_PRORESPRICELABEL = 'espl_autocomplete/design/pro_result_price_label_color';

    const XML_PATH_PRORESPRICELABELHOV = 'espl_autocomplete/design/pro_result_price_label_color_hov';
    private $price;

    private $store;

    private $cache;

    public $scopeConfig;

    public function __construct(
        \Magento\Framework\Locale\Format $price,
        \Magento\Store\Model\StoreManagerInterface $store,
        \Magento\Framework\App\CacheInterface $cache,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
    
        $this->price = $price;
        $this->store = $store;
        $this->cache = $cache;
        $this->scopeConfig = $scopeConfig;
    }

    //Check if Extension is  Enabled or not
    public function isEnabled()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $this->scopeConfig->getValue(self::XML_PATH_ENABLE, $storeScope);
    }

    //Get the Product Limit
    public function getLimit()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        if ($this->scopeConfig->getValue(self::XML_PATH_LIMIT, $storeScope) == null) {
            return 3;
        }

        return $this->scopeConfig->getValue(self::XML_PATH_LIMIT, $storeScope);
    }

    //Get the Category Limit
    public function getCategotyLimit()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        if ($this->scopeConfig->getValue(self::XML_PATH_CATELIMIT, $storeScope) == null) {
            return 5;
        }

        return $this->scopeConfig->getValue(self::XML_PATH_CATELIMIT, $storeScope);
    }

    //Get the Min Length
    public function getMinLength()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        if ($this->scopeConfig->getValue(self::XML_PATH_MINLENGTH, $storeScope) == null) {
            return 1;
        }

        return $this->scopeConfig->getValue(self::XML_PATH_MINLENGTH, $storeScope);
    }

    //Get the CacheLifeTime
    public function getCacheLifetime()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        if ($this->scopeConfig->getValue(self::XML_PATH_CACHETIME, $storeScope) == null) {
            return 86400;
        }

        return $this->scopeConfig->getValue(self::XML_PATH_CACHETIME, $storeScope);
    }

    //Get the Local Storage
    public function getUseLocalStorage()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $this->scopeConfig->getValue(self::XML_PATH_LOCALSTORAGE, $storeScope);
    }

    //Get the View Product Config
    public function getViewProductConf()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $this->scopeConfig->getValue(self::XML_PATH_SHOWPRODUCTS, $storeScope);
    }

    //Get the View Category Config
    public function getViewCategoryConf()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $this->scopeConfig->getValue(self::XML_PATH_SHOWCATEGORY, $storeScope);
    }

    //Get the Js Format Price
    public function getJsPriceFormat()
    {
        $priceFormat = $this->price;
        return $priceFormat->getPriceFormat();
    }

    //Get the Base Url
    public function getBaseUrl()
    {
        $storeManager = $this->store;
        return $storeManager->getStore()->getBaseUrl();
    }

    //Get the Media Url
    public function getBaseUrlMedia()
    {
        $storeManager = $this->store;
        return $storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }

    //Get the return Cache Data
    public function getCacheData($name)
    {
        $cache1_filename = hash('sha256', $name);
        if (false !== ($data = $this->cache->load($cache1_filename))) {
            return $data;
        } else {
            return 0;
        }
    }

    //Store Cache Data
    public function storecachedata($name, $Cachedata)
    {
        $cache1_filename = hash('sha256', $name);
        $this->cache->save($Cachedata, $cache1_filename, ['espl_autocomplete']);
    }

    //Get the Category Header text Color
    public function getCategotyHeadertextcolor()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        if ($this->scopeConfig->getValue(self::XML_PATH_CATHEDTEXT, $storeScope) == null) {
            return '#fff';
        }

        return $this->scopeConfig->getValue(self::XML_PATH_CATHEDTEXT, $storeScope);
    }

    //Get the Category Header Background Color
    public function getCategotyHeaderbackground()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        if ($this->scopeConfig->getValue(self::XML_PATH_CATHEDBACK, $storeScope) == null) {
            return '#1270a3';
        }

        return $this->scopeConfig->getValue(self::XML_PATH_CATHEDBACK, $storeScope);
    }

    //Get the Category Result text Color
    public function getCategotyResulttextcolor()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        if ($this->scopeConfig->getValue(self::XML_PATH_CATRESTEXT, $storeScope) == null) {
            return '#1270a3';
        }

        return $this->scopeConfig->getValue(self::XML_PATH_CATRESTEXT, $storeScope);
    }

    //Get the Category Result Background Color
    public function getCategotyResultbackground()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        if ($this->scopeConfig->getValue(self::XML_PATH_CATRESBACK, $storeScope) == null) {
            return '#fff';
        }

        return $this->scopeConfig->getValue(self::XML_PATH_CATRESBACK, $storeScope);
    }

    //Get the Category Result text hover Color
    public function getCategotyResulttextcolorhover()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        if ($this->scopeConfig->getValue(self::XML_PATH_CATRESTEXTHOV, $storeScope) == null) {
            return '#1270a3';
        }

        return $this->scopeConfig->getValue(self::XML_PATH_CATRESTEXTHOV, $storeScope);
    }

    //Get the Product Header Text Color
    public function getProductHeadertextcolor()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        if ($this->scopeConfig->getValue(self::XML_PATH_PROHEDTEXT, $storeScope) == null) {
            return '#fff';
        }

        return $this->scopeConfig->getValue(self::XML_PATH_PROHEDTEXT, $storeScope);
    }

    //Get the Product Header Background Color
    public function getProductHeaderbackground()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        if ($this->scopeConfig->getValue(self::XML_PATH_PROHEDBACK, $storeScope) == null) {
            return '#1270a3';
        }

        return $this->scopeConfig->getValue(self::XML_PATH_PROHEDBACK, $storeScope);
    }

    //Get the Product Result Text Color
    public function getProductResulttextcolor()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        if ($this->scopeConfig->getValue(self::XML_PATH_PRORESTEXT, $storeScope) == null) {
            return '#1270a3';
        }

        return $this->scopeConfig->getValue(self::XML_PATH_PRORESTEXT, $storeScope);
    }

    //Get the Product Result Background Color
    public function getProductResultbackground()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        if ($this->scopeConfig->getValue(self::XML_PATH_PRORESBACK, $storeScope) == null) {
            return '#fff';
        }

        return $this->scopeConfig->getValue(self::XML_PATH_PRORESBACK, $storeScope);
    }

        //Get the Product Result Text hover Color
    public function getProductResulttexthovercolor()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        if ($this->scopeConfig->getValue(self::XML_PATH_PRORESTEXTHOV, $storeScope) == null) {
            return '#1270a3';
        }

        return $this->scopeConfig->getValue(self::XML_PATH_PRORESTEXTHOV, $storeScope);
    }

    //Get the Product Header Text
    public function getProductHeadertext()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        if ($this->scopeConfig->getValue(self::XML_PATH_PROHEADERTEXT, $storeScope) == null) {
            return 'SUGGESTED PRODUCTS';
        }

        return $this->scopeConfig->getValue(self::XML_PATH_PROHEADERTEXT, $storeScope);
    }

    //Get the Category Header Text
    public function getCategoryHeadertext()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        if ($this->scopeConfig->getValue(self::XML_PATH_CATHEADERTEXT, $storeScope) == null) {
            return 'CATEGORIES';
        }

        return $this->scopeConfig->getValue(self::XML_PATH_CATHEADERTEXT, $storeScope);
    }

        //Get the Product Result Price Text Color
    public function getProductResultpricetextcolor()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        if ($this->scopeConfig->getValue(self::XML_PATH_PRORESPRICETEXT, $storeScope) == null) {
            return '#FF9900';
        }

        return $this->scopeConfig->getValue(self::XML_PATH_PRORESPRICETEXT, $storeScope);
    }

    //Get the Product Result Price Text Color
    public function getProductResultpricetextcolorhover()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        if ($this->scopeConfig->getValue(self::XML_PATH_PRORESPRICETEXTHOV, $storeScope) == null) {
            return '#FF9900';
        }

        return $this->scopeConfig->getValue(self::XML_PATH_PRORESPRICETEXTHOV, $storeScope);
    }

    //Get the Product Result Price label Text Color
    public function getProductResultpricelabeltextcolor()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        if ($this->scopeConfig->getValue(self::XML_PATH_PRORESPRICELABEL, $storeScope) == null) {
            return '#1270a3';
        }

        return $this->scopeConfig->getValue(self::XML_PATH_PRORESPRICELABEL, $storeScope);
    }

    //Get the Product Result Price label Text Color
    public function getProductResultpricelabeltexthovercolor()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        if ($this->scopeConfig->getValue(self::XML_PATH_PRORESPRICELABELHOV, $storeScope) == null) {
            return '#1270a3';
        }

        return $this->scopeConfig->getValue(self::XML_PATH_PRORESPRICELABELHOV, $storeScope);
    }
}
