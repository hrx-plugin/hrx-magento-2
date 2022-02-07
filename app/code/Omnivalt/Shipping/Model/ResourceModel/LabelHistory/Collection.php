<?php

namespace Omnivalt\Shipping\Model\ResourceModel\LabelHistory;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection {

    protected $_idFieldName = 'labelhistory_id';
    protected $_eventPrefix = 'omnivalt';
    protected $_eventObject = 'label_history_collection';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct() {
        $this->_init('Omnivalt\Shipping\Model\LabelHistory', 'Omnivalt\Shipping\Model\ResourceModel\LabelHistory');
    }

}