<?php
namespace Omnivalt\Shipping\Block\Adminhtml\Sales;

use  Magento\Sales\Model\OrderRepository;

class Terminal extends \Magento\Backend\Block\Template {
   
    protected $Omnivalt_carrier;
    
   
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = [], 
        \Omnivalt\Shipping\Model\Carrier $Omnivalt_carrier
    ) {
        $this->coreRegistry = $registry;
        $this->Omnivalt_carrier = $Omnivalt_carrier;
        parent::__construct($context, $data);
    }
    
    public function getTerminalName(){
        //$orderRepository = new \Magento\Sales\Model\OrderRepository();
        $order_id = $this->getRequest()->getParam('order_id');
        $order = $this->getOrder();
        //$order =  $orderRepository->get($order_id);
        if (strtoupper($order->getData('shipping_method')) == strtoupper('Omnivalt_PARCEL_TERMINAL')) {
            return $this->getTerminal($order);
        }
        return false;
    
    }
    
    public function getTerminal($order)
    {
        $shippingAddress = $order->getShippingAddress();
        $terminal_id = $shippingAddress->getOmnivaltParcelTerminal();
        $parcel_terminal = $this->Omnivalt_carrier->getTerminalAddress($terminal_id);
        return $parcel_terminal;
   } 
    
    /**
     * Retrieve order model instance
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->coreRegistry->registry('current_order');
    }
    
}