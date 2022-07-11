<?php

namespace Hrx\Shipping\Model;

class HrxOrder extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{

    const CACHE_TAG = 'hrx_order';

    protected function _construct() {
        $this->_init('Hrx\Shipping\Model\ResourceModel\HrxOrder');
    }

    public function getIdentities() {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

}
