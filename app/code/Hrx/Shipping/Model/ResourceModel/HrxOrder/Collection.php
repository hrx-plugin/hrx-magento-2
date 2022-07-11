<?php

namespace Hrx\Shipping\Model\ResourceModel\HrxOrder;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection {

    protected $_idFieldName = 'id';
    protected $_eventPrefix = 'hrx';
    protected $_eventObject = 'hrx_order_collection';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct() {
        $this->_init('Hrx\Shipping\Model\HrxOrder', 'Hrx\Shipping\Model\ResourceModel\HrxOrder');
    }

}