<?php

namespace Hrx\Shipping\Model;

class HrxLocation extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{

    const CACHE_TAG = 'hrx_location';

    protected function _construct() {
        $this->_init('Hrx\Shipping\Model\ResourceModel\HrxLocation');
    }

    public function getIdentities() {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

}
