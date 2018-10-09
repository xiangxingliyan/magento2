<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018-5-7
 * Time: 下午 12:29
 */
//
namespace Ave40\Inquire\Controller\Adminhtml;

abstract class Records extends \Magento\Backend\App\Action
{
    protected $resultPageFactory;
    protected $_request;
    protected $_fileSystem;
    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Filesystem $_filesystem
    ) {
        parent::__construct($context);
        
        $this->resultPageFactory = $resultPageFactory;
        $this->_request = $request;
        $this->_fileSystem = $_filesystem;
    }
  
    protected function _redirectToIndex() {
        return $this->_redirect('*/*/index');
    }
    
    protected function _redirectToEdit($id=null) {
        if($id) {
            return $this->_redirect('*/*/edit', ['id' => $id]);
        } else {
            return $this->_redirect('*/*/edit');
        }
    }
}
