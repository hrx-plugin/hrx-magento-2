<?php

namespace Hrx\Shipping\Model\ResourceModel\HrxWarehouse;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection {

    protected $_idFieldName = 'id';
    protected $_eventPrefix = 'hrx';
    protected $_eventObject = 'hrx_warehouse_collection';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct() {
        $this->_init('Hrx\Shipping\Model\HrxWarehouse', 'Hrx\Shipping\Model\ResourceModel\HrxWarehouse');
    }

}