<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018-6-12
 * Time: 下午 05:04
 */
namespace Ave40\Base\Model\Api;
use Ave40\Base\Api\ProductInterface;

class ProductApi extends ApiAbstract implements ProductInterface {
    /** @var \Magento\Catalog\Model\ProductFactory  */
    protected $_productFactory;
    /** @var \Magento\Catalog\Model\ResourceModel\Product  */
    protected $_resourceModel;
    /** @var \Magento\Catalog\Model\ProductRepository  */
    protected $_productRepository;
    protected $_productCollectionFactory;
    protected $_productModel;
    protected $_stockItem;

    /**
     * ProductApi constructor.
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product $resourceModel
     * @param \Magento\Catalog\Model\ProductRepository $productRepository
     */
    public function __construct(
        \Magento\Catalog\Model\Product $productModel,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\ResourceModel\Product $resourceModel,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\CatalogInventory\Model\Stock\Item $stockItem
    )
    {
        $this->_productModel = $productModel;
        $this->_productFactory = $productFactory;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_resourceModel = $resourceModel;
        $this->_productRepository = $productRepository;
        $this->_stockItem = $stockItem;
    }
    
    /**
     * @api
     * @param string $productId
     * @param string $productSku
     * @param string $productItemnum
     * @return mixed|string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function idconvert($productId=null, $productSku=null, $productItemnum=null) {
        $resultData = [];
        $attributes = ['sku', 'itemnum'];
        
        if($productId) {
            /** @var \Magento\Catalog\Model\Product $product */
            $product = $this->_productFactory->create();
            $this->_resourceModel->load($product, $productId, $attributes);
            
            if($product->getId()) {
                $resultData['productId'] = [
                    'id' => $product->getId(),
                    'sku' => $product->getSku(),
                    'itemnum' => $product->getData('itemnum')
                ];
            } else {
                $resultData['productId'] = [];
            }
        }
        
        if($productSku) {
            $product = $this->_productRepository->get($productSku);
    
            if($product->getId()) {
                $resultData['productSku'] = [
                    'id' => $product->getId(),
                    'sku' => $product->getSku(),
                    'itemnum' => $product->getData('itemnum')
                ];
            } else {
                $resultData['productSku'] = [];
            }
        }
    
        if($productItemnum) {
            $productCollection = $this->_productCollectionFactory->create();
            $productCollection->addAttributeToFilter('itemnum', $productItemnum);
            $productCollection->setPage(1, 1);
            $product = $productCollection->getFirstItem();
        
            if($product->getId()) {
                $resultData['productItemnum'] = [
                    'id' => $product->getId(),
                    'sku' => $product->getSku(),
                    'itemnum' => $product->getData('itemnum')
                ];
            } else {
                $resultData['productItemnum'] = [];
            }
        }
        
        return $this->makeReturn($resultData);
    }

    /**
     * @api
     * @param string $data
     * [
     * 'identifierType' => 'itemnum',
     * 'data' => [
     * 'WOOAT0022' => [
     * 'tag' => 1,
     * 'stock_item' => [
     * 'qty' => 112,
     * 'is_in_stock' => 1
     * ]
     * ]
     * ]
     * ]
     * @return mixed|string
     */
    public function updateProductQtyBatch($data=null)
    {
        $data = json_decode($data, true);
        $return = ['success' => 0, 'message' => '', 'data' => []];
        if (!empty($data['data'])) {
            $jsonData = [];
            foreach ($data['data'] as $itemnum => $da) {
                try {
                    $stockData = $da['stock_item'];
                    /** @var \Magento\Catalog\Model\Product $product */
                    $product = $this->_productModel->loadByAttribute('itemnum', $itemnum);
                    if (empty($product) || empty($product->getId())) {
                        $jsonData['product_not_exists'][] = $itemnum;
                        continue;
                    }
                    /** @var \Magento\CatalogInventory\Model\Stock\Item $stockItem */
                    $stockItem = $this->_stockItem;
                    $stockItem->load($product->getId(), 'product_id');
                    if (isset($stockData['qty'])) {
                        $qty = empty((int)$stockData['qty']) ? 0 : (int)$stockData['qty'];
                        $isStock = $qty == 0 ? 0 : 1;
                        if (!$stockItem->getProductId()) {
                            $stockItem->setProductId($product->getId());
                        }
                        $stockItem->setUseConfigManageStock(1);
                        $stockItem->setQty($qty);
                        $stockItem->setIsInStock($isStock);
                        $stockItem->setIsQtyDecimal(0);
                        $stockItem->save();
                        $return['data']['success'][] = $itemnum;
                    } else {
                        $jsonData['qty_is_null'][] = $itemnum;
                    }
                } catch (\Exception $e) {
                    $jsonData[$e->getMessage()][] = $itemnum;
                }

            }
            if (empty($jsonData)) {
                $return['success'] = 1;
                $return['message'] = 'all_success';
            } else {
                $return['message'] = 'some_mistakes';
                $return['data']['failed'] = $jsonData;
            }
        } else {
            $return['message'] = 'no_product_data';
        }
        return $this->makeReturn($return);
    }
    
}