<?php

namespace Hrx\Shipping\Model;

class HrxWarehouse extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{

    const CACHE_TAG = 'hrx_warehouse';

    protected function _construct() {
        $this->_init('Hrx\Shipping\Model\ResourceModel\HrxWarehouse');
    }

    public function getIdentities() {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

}
