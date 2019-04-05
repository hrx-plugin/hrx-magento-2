<?php

namespace Omnivalt\Shipping\Model;

use Magento\Framework\DataObject;
use Omnivalt\Shipping\Api\Data\ParcelTerminalInterface;

class ParcelTerminal extends DataObject implements ParcelTerminalInterface
{
    /**
     * @return string
     */
    public function getZip()
    {
        return (string)$this->_getData('zip');
    }

    /**
     * @return string
     */
    public function getName()
    {
        return (string)$this->_getData('name');
    }

    /**
     * @return string
     */
    public function getLocation()
    {
        return (string)$this->_getData('location');
    }
}