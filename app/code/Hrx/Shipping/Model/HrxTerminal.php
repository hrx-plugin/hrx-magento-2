<?php

namespace Hrx\Shipping\Model;

class HrxTerminal extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{

    const CACHE_TAG = 'hrx_terminal';

    protected function _construct() {
        $this->_init('Hrx\Shipping\Model\ResourceModel\HrxTerminal');
    }

    public function getIdentities() {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

}
