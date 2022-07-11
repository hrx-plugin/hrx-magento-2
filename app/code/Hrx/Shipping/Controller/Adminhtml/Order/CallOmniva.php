<?php

namespace Hrx\Shipping\Controller\Adminhtml\Order;

use Magento\Backend\App\Action\Context;

/**
 * Class MassManifest
 */
class CallHrx extends \Magento\Framework\App\Action\Action
{

    protected $hrx_carrier;

    public function __construct(
            Context $context, 
            \Hrx\Shipping\Model\Carrier $hrx_carrier) {
        $this->hrx_carrier = $hrx_carrier;
        parent::__construct($context);
    }

    public function execute() {

        $result = $this->hrx_carrier->callHrx();
        if ($result) {
            $text = __('Hrx courier called');
            $this->messageManager->addSuccess($text);
        } else {
            $text = __('Failed to call Hrx courier');
            $this->messageManager->addWarning($text);
        }
        $this->_redirect($this->_redirect->getRefererUrl());
        return;
    }

}
