<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018-08-29 0029
 * Time: 上午 11:00
 */

namespace Ave40\Base\Helper;

use Magento\Framework\App\Helper\Context;

class Template extends \Magento\Framework\App\Helper\AbstractHelper {
    protected $blockFactory;
    
    public function __construct(Context $context, \Magento\Framework\View\Element\BlockFactory $blockFactory)
    {
        parent::__construct($context);
        
        $this->blockFactory = $blockFactory;
    }
    
    /**
     * 渲染简单模板
     * @param $template
     * @param $value
     * @return string
     */
    public function render($template, $value=[]) {
        /** @var \Magento\Framework\View\Element\Template $block */
        $block = $this->blockFactory->createBlock('Magento\Framework\View\Element\Template');
        $block->setData($value);
        $block->setTemplate($template);
        return $block->toHtml();
    }
}