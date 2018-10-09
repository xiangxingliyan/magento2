<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/29 0029
 * Time: 12:07
 */
namespace Ave40\Base\Block\Adminhtml\System\Config;
use \Magento\Framework\Stdlib\DateTime;

class Date extends \Magento\Config\Block\System\Config\Form\Field
{
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $element->setDateFormat(DateTime::DATE_INTERNAL_FORMAT);
        $element->setTimeFormat('HH:mm:ss');
        $element->setShowsTime(true);
        return parent::render($element);
    }
}