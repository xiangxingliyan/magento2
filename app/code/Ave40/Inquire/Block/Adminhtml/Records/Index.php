<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017-10-20
 * Time: 下午 05:57
 */
namespace Ave40\Inquire\Block\Adminhtml\Records;

class Index extends  \Magento\Backend\Block\Widget\Grid\Container
{
    public function _construct()
    {
        $this->_controller = 'inquire_admin';
        $this->_headerText = __('询盘记录');
        $this->_blockGroup = 'Ave40_Inquire';
        parent::_construct();
    }
}