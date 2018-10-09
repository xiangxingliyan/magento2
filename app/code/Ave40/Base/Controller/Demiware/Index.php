<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/21 0021
 * Time: 15:35
 */

namespace Ave40\Base\Controller\Demiware;

use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session;

class Index extends \Magento\Framework\App\Action\Action
{
    protected $_resultPageFactory;

    /**
     * @var Session
     */
    protected $session;

    protected $_demiwareHelper;

    public function __construct(
        Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        Session $customerSession,
        \Ave40\Base\Helper\Demiware\Demiware $demiwareHelper
    )
    {
        $this->_resultPageFactory = $resultPageFactory;
        $this->session = $customerSession;
        $this->_demiwareHelper = $demiwareHelper;
        parent::__construct($context);
    }

    public function execute()
    {
        $resultPage = $this->_resultPageFactory->create();
        return $resultPage;

    }
}