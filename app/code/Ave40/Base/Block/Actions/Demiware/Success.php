<?php
namespace Ave40\Base\Block\Actions\Demiware;
use Magento\Customer\Model\Context;
class Success extends \Magento\Framework\View\Element\Template
{
    protected $msg = 'Success';
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

    protected $_demiwareHelper;

    /**
     * @var \Magento\Framework\Data\Helper\PostHelper
     */
    protected $_postDataHelper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param \Magento\Customer\Model\Url $customerUrl
     * @param \Magento\Framework\Data\Helper\PostHelper $postDataHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Customer\Model\Url $customerUrl,
        \Magento\Framework\Data\Helper\PostHelper $postDataHelper,
        \Ave40\Base\Helper\Demiware\Demiware $demiwareHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->httpContext = $httpContext;
        $this->_customerUrl = $customerUrl;
        $this->_postDataHelper = $postDataHelper;
        $this->_demiwareHelper = $demiwareHelper;
    }

    public function getInfo()
    {
        if (!$this->_demiwareHelper->demiwareIsActive()) {
            $this->msg = 'Expired Promotion!';
        } elseif (!$this->isLoggedIn()) {
            $this->msg = '还没有登录,请先登录!';
        }

        return $this->msg;
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

}