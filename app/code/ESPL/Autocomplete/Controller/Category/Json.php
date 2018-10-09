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
 * @category     Elitech
 * @package      ESPL_Autocomplete
 * @author-email info@elitechsystems.com
 * @copyright    Copyright 2017 � elitechsystems.com. All Rights Reserved
 */

namespace ESPL\Autocomplete\Controller\Category;

class Json extends \Magento\Framework\App\Action\Action
{

  private $helper;

  private $storeManager;

  private $Categorycollcetion;

  private $categorymodel;

  public function __construct(
      \Magento\Backend\App\Action\Context $context,
      \Magento\Framework\View\Result\PageFactory $resultPageFactory,
      \ESPL\Autocomplete\Helper\Data $helper,
      \Magento\Catalog\Model\CategoryFactory $Categorycollcetion,
      \Magento\Store\Model\StoreManagerInterface $store,
      \Magento\Catalog\Model\Category $categorymodel
  ) {
    parent::__construct($context);
    $this->resultPageFactory = $resultPageFactory;
    $this->helper = $helper;
    $this->storeManager = $store;
    $this->Categorycollcetion = $Categorycollcetion;
    $this->categorymodel = $categorymodel;
  }

public function execute()
{
    $helper = $this->helper;
    if (0 === ($data = $helper->getCacheData('espl_autocomplete_categorycollcetion'))) {
        $categoriescollcetion = $this->categorymodel->getCollection()->addAttributeToSelect('name')
        ->addAttributeToSelect('default_sort_by')->addAttributeToSort('position', 'desc')
        ->addAttributeToFilter('is_active', [
            'in' =>  [
                1
                ]
            ])->addAttributeToFilter('level', [
            'in' =>  [
                2,
                3,4,5
                ]
            ])->load()->toArray();

            $categoryarray =  [];
            foreach ($categoriescollcetion as $categoryId => $category) {
                if ($category ['parent_id'] == $this->storeManager->getStore()->getRootCategoryId()) {
                    $categoryarray[$categoryId] = [
                    'name' => $category ['name'],
                    'type_id' => '',
                    'sku' => '',
                    'image' => $this->getcategoryImages($categoryId),
                    'url_path' => $this->getcategoryUrl($categoryId),
                    'min_price' => '',
                    'price' => '',
                    'final_price' => '',
                    'max_price' => '',
                    'type' => 'category'
                    ];
                } else {
                    $categoryarray[$categoryId] = [
                    'name' => $this->getcategoryName($category ['parent_id']).' > '.$category ['name'],
                    'type_id' => '',
                    'sku' => '',
                    'image' => $this->getcategoryImages($categoryId),
                    'url_path' => $this->getcategoryUrl($categoryId),
                    'min_price' => '',
                    'price' => '',
                    'final_price' => '',
                    'max_price' => '',
                    'type' => 'category'
                    ];
                }
            }

            $finalcategoryarray = [];
            $count = 0;
            foreach ($categoryarray as $key => $value) {
                $finalcategoryarray[$count] = $categoryarray[$key];
                $count++;
            }

            $data = json_encode($finalcategoryarray);
            $lifetime = $helper->getCacheLifetime();
              //Store Cache Data
            $helper->StoreCacheData('espl_autocomplete_categorycollcetion', $data);
    }

        $this->getResponse()
        ->setHeader('Content-Type', 'application/json')
        ->setBody($data);
}

    private function loadcategory($_category)
    {
        return $this->Categorycollcetion->create()->load($_category);
    }

    private function getcategoryImages($entity_id)
    {
        $category = $this->loadcategory($entity_id);
        return $category->getImageUrl();
    }

    private function getcategoryUrl($entity_id)
    {
        $category = $this->loadcategory($entity_id);
        return $category->getUrl();
    }

    private function getcategoryName($entity_id)
    {
        $category = $this->loadcategory($entity_id);
        return $category->getName();
    }
}
