<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/26 0026
 * Time: 19:24
 */
namespace Ave40\Base\Controller\Adminhtml\Actions;

abstract class Demiware extends \Magento\Backend\App\Action
{
    protected $resultPageFactory;
    protected $_request;
    protected $_fileSystem;
    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Framework\Filesystem $_filesystem
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