<?php

namespace Hrx\Shipping\Model\ResourceModel;

class HrxWarehouse extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb {

    public function __construct(
            \Magento\Framework\Model\ResourceModel\Db\Context $context
    ) {
        parent::__construct($context);
    }

    protected function _construct() {
        $this->_init('hrx_warehouses', 'id');
    }

    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object) {
        
    }

}