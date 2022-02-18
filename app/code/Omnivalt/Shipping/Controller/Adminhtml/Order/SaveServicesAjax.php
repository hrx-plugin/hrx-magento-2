<?php

namespace Omnivalt\Shipping\Controller\Adminhtml\Order;

use Magento\Backend\App\Action;

class SaveServicesAjax extends \Magento\Sales\Controller\Adminhtml\Order
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */

    public function execute()
    {
        $order = $this->_initOrder();
        if ($order) {
            $params = $this->getRequest()->getParams();
            $services = array();
            if (isset($params['omniva_services'])){
                $services = $params['omniva_services'];
            }
            $resultJson = $this->resultJsonFactory->create();
            $order->setOmnivaltServices(json_encode(array('services'=>$services)));
            $order->save();
            return $resultJson->setData([
                'messages' => 'Successfully.' ,
                'error' => false
            ]);
        }
        return false;
    }
}