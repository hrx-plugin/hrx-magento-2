<?php

namespace Omnivalt\Shipping\Controller\Adminhtml\Order;

use Magento\Backend\App\Action\Context;

/**
 * Class MassManifest
 */
class CallOmniva extends \Magento\Framework\App\Action\Action
{

    protected $omnivalt_carrier;

    public function __construct(
            Context $context, 
            \Omnivalt\Shipping\Model\Carrier $omnivalt_carrier) {
        $this->omnivalt_carrier = $omnivalt_carrier;
        parent::__construct($context);
    }

    public function execute() {

        $result = $this->omnivalt_carrier->callOmniva();
        if ($result) {
            $text = __('Omniva courier called');
            $this->messageManager->addSuccess($text);
        } else {
            $text = __('Failed to call Omniva courier');
            $this->messageManager->addWarning($text);
        }
        $this->_redirect($this->_redirect->getRefererUrl());
        return;
    }

}
