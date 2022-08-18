<?php

namespace Hrx\Shipping\Model\ResourceModel\HrxLocation;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection {

    protected $_idFieldName = 'id';
    protected $_eventPrefix = 'hrx';
    protected $_eventObject = 'hrx_location_collection';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct() {
        $this->_init('Hrx\Shipping\Model\HrxLocation', 'Hrx\Shipping\Model\ResourceModel\HrxLocation');
    }

}