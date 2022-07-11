<?php

namespace Hrx\Shipping\Model;

class LabelHistory extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{

    const CACHE_TAG = 'hrx_label_history';

    protected function _construct() {
        $this->_init('Hrx\Shipping\Model\ResourceModel\LabelHistory');
    }

    public function getIdentities() {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

}
