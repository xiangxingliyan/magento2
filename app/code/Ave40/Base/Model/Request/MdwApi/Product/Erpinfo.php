<?php
namespace Ave40\Base\Model\Request\MdwApi\Product;
/**
 * 获取erp产品信息接口
 * Class \Ave40\Base\Model\Request\MdwApi\Product\Erpinfo
 * @package Ave40\Base\Model\Request\MdwApi\Product
 *
 * @method setItems($items)
 *  设置itemnum, 可以用数组指定多个
 * @method getItems()
 */
class Erpinfo extends \Ave40\Base\Model\Request\MdwApi\Tokenbase
{
    protected $_path = "/api/product/getErpProduct";
    
    public function send()
    {
        $this->setRequestData('data', $this->_createRequestParams());
        return parent::send();
    }
    
    protected function _createRequestParams() {
        $items = $this->getItems();
    
        if(!is_array($items)) {
            if(empty($items)) {
                $this->setItems([]);
            } else {
                $this->setItems([$items]);
            }
        }
        
        return json_encode($this->getData());
    }
}