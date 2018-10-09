<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/21 0021
 * Time: 15:35
 */
namespace Ave40\Base\Controller\Demiware;
use Magento\Framework\App\Action\Context;

class Success extends \Magento\Framework\App\Action\Action
{
    protected $_resultPageFactory;

    public function __construct(Context $context, \Magento\Framework\View\Result\PageFactory $resultPageFactory)
    {
        $this->_resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    public function execute()
    {

        $resultPage = $this->_resultPageFactory->create();
        return $resultPage;
    }
}
