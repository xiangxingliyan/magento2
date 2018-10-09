<?php

namespace Ave40\Base\Ui\Component\Columns;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;

class OrderGrid extends \Magento\Ui\Component\Listing\Columns\Column
{
    protected $_records;
    
    public function __construct(ContextInterface $context, UiComponentFactory $uiComponentFactory, array $components = [], array $data = [],\Ave40\Base\Model\Mdw\Records $records)
    {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->_records = $records;
    }
    
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $records = $this->_records;
            $entityIds = array_column($dataSource['data']['items'],'entity_id');
            $recordsCollection = $records->getCollection();
            $recordsCollection->addFieldToFilter('entity_id', ['in' => $entityIds]);
            $recordsCollection->addFieldToFilter('type', \Ave40\Base\Model\Mdw\Records::TYPE_ORDER_UPLOAD);
            $allSyncItems = [];
            foreach ($recordsCollection as $row) {
                $allSyncItems[$row->getEntityId()] = $row;
            }
            
            $htmlTemplate = '<span style="color:%s;">%s</span>';
            foreach ($dataSource['data']['items'] as &$item) {
                //put logic here
                $status = sprintf($htmlTemplate, 'gray', '-');
                
                if(isset($allSyncItems[$item['entity_id']])) {
                    $syncItem = $allSyncItems[$item['entity_id']];
    
                    if($syncItem->getStatus() == \Ave40\Base\Model\Mdw\Records::STATUS_ARRIVED_DMW) {
                        $status = sprintf($htmlTemplate, 'blue', '已同步,待付');
                    }
    
                    if($syncItem->getStatus() == \Ave40\Base\Model\Mdw\Records::STATUS_COMPLETED) {
                        $status = sprintf($htmlTemplate, 'green', '已同步');
                    }
    
                    if($syncItem->getStatus() == \Ave40\Base\Model\Mdw\Records::STATUS_PROCESSING) {
                        $status = sprintf($htmlTemplate, 'orange', '同步中');
                    }
    
                    if($syncItem->getStatus() == \Ave40\Base\Model\Mdw\Records::STATUS_FAILURE) {
                        $status = sprintf($htmlTemplate, 'red', '同步失败');
                    }
                }
                $item[$this->getData('name')] = $status;
            }
        }
        return $dataSource;
    }
}