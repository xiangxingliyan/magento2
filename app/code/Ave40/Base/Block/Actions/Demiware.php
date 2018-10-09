<?php

namespace Ave40\Base\Block\Actions;

use Magento\Customer\Model\Context;

class Demiware extends \Magento\Framework\View\Element\Template
{
    const DEFAULT_PRODUCT_NAME = "VLADDIN RE Refillable Pod System Kit";
    const DEFAULT_PRODUCT_COLOR = "Black/Gold/Rainbow/Titanium";
    const DEFAULT_PRODUCT_PRICE1 = "$29.9";
    const DEFAULT_PRODUCT_PRICE2 = "$31.9";

    /**
     * @var \Magento\Framework\Data\Form\FormKey
     */
    protected $formKey;

    /**
     * Customer session
     *
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * @var \Magento\Customer\Model\Url
     */
    protected $_customerUrl;

    /**
     * @var \Magento\Framework\Data\Helper\PostHelper
     */
    protected $_postDataHelper;

    /**
     * @var \Ave40\Base\Helper\Demiware\Demiware
     */
    protected $_demiwareHelper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param \Magento\Customer\Model\Url $customerUrl
     * @param \Magento\Framework\Data\Helper\PostHelper $postDataHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Data\Form\FormKey $formKey,
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Customer\Model\Url $customerUrl,
        \Magento\Framework\Data\Helper\PostHelper $postDataHelper,
        \Ave40\Base\Helper\Demiware\Demiware $demiwareHelper,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->formKey = $formKey;
        $this->httpContext = $httpContext;
        $this->_customerUrl = $customerUrl;
        $this->_postDataHelper = $postDataHelper;
        $this->_demiwareHelper = $demiwareHelper;
    }

    public function getDemiwareHelper()
    {
        return $this->_demiwareHelper;
    }

    /**
     * Is logged in
     *
     * @return bool
     */
    public function isLoggedIn()
    {
        return $this->httpContext->getValue(Context::CONTEXT_AUTH);
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->isLoggedIn() ? __('Sign Out') : __('Sign In');

    }

    public function getPostActionUrl()
    {
        return $this->getUrl('base/demiware/editpost');
    }

    public function getProductInfo()
    {
        $info = [];
        $configInfo = $this->_demiwareHelper->getProductInfo();
        if (!empty($configInfo)) {
            $configInfo = explode('||', $configInfo);
        } else {
            $configInfo = [];
        }
        $info['name'] = !empty(trim($configInfo[0])) ? $configInfo[0] : self::DEFAULT_PRODUCT_NAME;
        $info['color'] = !empty(trim($configInfo[1])) ? $configInfo[1] : self::DEFAULT_PRODUCT_COLOR;
        $info['price1'] = !empty(trim($configInfo[2])) ? $configInfo[2] : self::DEFAULT_PRODUCT_PRICE1;
        $info['price2'] = !empty(trim($configInfo[3])) ? $configInfo[3] : self::DEFAULT_PRODUCT_PRICE2;

        return $info;
    }

    /**
     * Retrieve form key
     *
     * @return string
     * @codeCoverageIgnore
     */
    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }

    //申请人数=实际申请人数+配置申请人数
    public function getTotalCount()
    {
        return (int)$this->_demiwareHelper->getApplications() + (int)$this->_demiwareHelper->getMinimum();
    }


}