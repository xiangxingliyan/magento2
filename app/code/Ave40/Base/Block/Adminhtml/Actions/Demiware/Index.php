<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/26 0026
 * Time: 18:39
 */

namespace Ave40\Base\Block\Adminhtml\Actions\Demiware;

class Index extends \Magento\Backend\Block\Widget\Grid\Container
{
//    protected $_template = 'Actions/Demiware/index.phtml';

    protected $_demiwareHelper;

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context
    )
    {
        $this->_controller = 'base_admin';
        $this->_headerText = __('申请记录');
        $this->_blockGroup = 'Ave40_Base';
        parent::__construct($context);
    }

}