<?php
/**
 * ESPL_Autocomplete extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Elitech Rest API License
 * that is bundled with this package in the file license.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.elitechsystems.com/license.txt
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@elitechsystems.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * versions in the future. If you wish to customize this extension for your
 * needs please refer to http://www.elitechsystems.com for more information.
 *
 * @category   Elitech
 * @package    ESPL_Autocomplete
 * @author-email  info@elitechsystems.com
 * @copyright  Copyright 2017 � elitechsystems.com. All Rights Reserved
 */

namespace ESPL\Autocomplete\Controller\Product;

class Json extends \Magento\Framework\App\Action\Action
{
  private $helper;

  private $store;

  private $productCollection;

  private $image;

  private $product;

  public function __construct(
      \Magento\Backend\App\Action\Context $context,
      \Magento\Framework\View\Result\PageFactory $resultPageFactory,
      \ESPL\Autocomplete\Helper\Data $helper,
      \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollection,
      \Magento\Store\Model\StoreManagerInterface $store,
      \Magento\Catalog\Helper\Image $image,
      \Magento\Catalog\Model\ProductFactory $product
  ) {
    parent::__construct($context);
    $this->resultPageFactory = $resultPageFactory;
    $this->helper = $helper;
    $this->productCollection = $productCollection;
    $this->store = $store;
    $this->image = $image;
    $this->product = $product;
  }

  public function execute()
  {
    $helper = $this->helper;
    //Check if the Cache is there
    if (0 === ($data = $helper->getCacheData('espl_autocomplete_productcollcetion'))) {
      $productCollection = $this->productCollection;
      //Fetch Product Collcetion
      $collection = $productCollection->create()
      ->addAttributeToSelect('*')
      ->load();

      $collection->joinAttribute('status', 'catalog_product/status', 'entity_id', null, 'inner');
      $collection->joinAttribute('visibility', 'catalog_product/visibility', 'entity_id', null, 'inner');
      $collection->addWebsiteNamesToResult();
      $collection->addFinalPrice();
      $collection->applyFrontendPriceLimitations();
      $storeManager = $this->store;
      $productarray = [];
      $Count = 0;
      $width = 150;
      $height = null;

      foreach ($collection as $value) {
        //Get teh product Image
        $imageUrl = $this->image
        ->init($value, 'product_page_image_small')
        ->setImageFile($value->getFile())
        ->getUrl();

        if ($value->getVisibility() != \Magento\Catalog\Model\Product\Visibility::VISIBILITY_NOT_VISIBLE) {
          if ($value->getTypeId()=='configurable') {
            $product = $this->loadproduct($value->getData('entity_id'));
            $productarray[$Count] = $this->getconfigproductdata($value, $product, $imageUrl);
            $Count++;
          } else {
            $product = $this->loadproduct($value->getData('entity_id'));
            $productarray[$Count] = $this->getsimpleproductdata($value, $product, $imageUrl);
            $Count++;
          }
        }
      }
      
      $data = json_encode($productarray);
      //get the Cache Life Time
      $lifetime = $helper->getCacheLifetime();
      //Store Cache Data
      $helper->storecachedata('espl_autocomplete_productcollcetion', $data);
    }

    $this->getResponse()
    ->setHeader('Content-Type', 'application/json')
    ->setBody($data);
  }

  private function loadproduct($entityid)
  {
    return $this->product->create()->load($entityid);
  }

  private function getconfigproductdata($value, $product, $imageUrl)
  {
    $additional['_escape'] = true;
    return [
        'entity_id' => $value->getData('entity_id'),
            'attribute_set_id' => $value->getData('attribute_set_id'),
            'type_id' => $value->getData('type_id'),
            'sku' => $value->getData('sku'),
            'has_options' => $value->getData('has_options'),
            'required_options' => $value->getData('required_options'),
            'created_at' => $value->getData('created_at'),
            'updated_at' => $value->getData('updated_at'),
            'name' => $value->getData('name'),
            'image' => $imageUrl,
            'url_path' => $product->getUrlModel()->getProductUrl($product, $additional),
            'price' => $product->getFinalPrice(),
            'tax_class_id' => $product->getTaxId(),
            'minimal_price' => $product->getFinalPrice(),
            'min_price' => $product->getFinalPrice(),
            'max_price' => $product->getFinalPrice(),
            'tier_price' => $product->getTierPrice()
      ];
  }

  private function getsimpleproductdata($value, $product, $imageUrl)
  {
    $additional['_escape'] = true;
    return [
      'entity_id' => $value->getData('entity_id'),
            'attribute_set_id' => $value->getData('attribute_set_id'),
            'type_id' => $value->getData('type_id'),
            'sku' => $value->getData('sku'),
            'has_options' => $value->getData('has_options'),
            'required_options' => $value->getData('required_options'),
            'created_at' => $value->getData('created_at'),
            'updated_at' => $value->getData('updated_at'),
            'name' => $value->getData('name'),
            'image' => $imageUrl,
            'url_path' => $product->getUrlModel()->getProductUrl($product, $additional),
            'price' => $product->getPriceInfo()->getPrice('final_price')->getValue(),
            'tax_class_id' => $value->getTaxId(),
            'minimal_price' => $value->getSpecialPrice(),
            'min_price' => $value->getSpecialPrice(),
            'max_price' => $value->getPrice(),
            'tier_price' =>$value->getTierPrice()
            ];
  }
}
