<?php

namespace Hrx\Shipping\Block\Adminhtml\Sales;

use Magento\Sales\Model\OrderRepository;
use Hrx\Shipping\Model\Carrier;

class Terminal extends \Magento\Backend\Block\Template
{

    protected $hrxCarrier;
    protected $data;
    private $productMetadata;

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
            Carrier $hrxCarrier,
            \Magento\Framework\App\ProductMetadataInterface $productMetadata
    ) {
        $this->coreRegistry = $registry;
        $this->hrxCarrier = $hrxCarrier;
        $this->data = $data;
        $this->productMetadata = $productMetadata;
        parent::__construct($context, $data);
    }

    public function getTerminalName() {
        $order = $this->getOrder();
        if (strtoupper($order->getData('shipping_method')) == strtoupper('hrx_parcel_terminal')) {
            return $this->getTerminal($order);
        }
        return false;
    }

    public function isHrxTerminal() {
        $order = $this->getOrder();
        return strtoupper($order->getData('shipping_method')) == strtoupper('hrx_parcel_terminal');
    }

    public function getCurrentTerminal() {
        //$orderRepository = new \Magento\Sales\Model\OrderRepository();
        $order_id = $this->getRequest()->getParam('order_id');
        $order = $this->getOrder();
        //$order =  $orderRepository->get($order_id);
        if (strtoupper($order->getData('shipping_method')) == strtoupper('Hrx_parcel_terminal')) {
            return $this->getTerminalId($order);
        }
        return false;
    }

    public function getTerminalId($order) {
        $shippingAddress = $order->getShippingAddress();
        $terminal_id = $shippingAddress->getHrxParcelTerminal();
        return $terminal_id;
    }

    public function getTerminal($order) {
        $shippingAddress = $order->getShippingAddress();
        $terminal_id = $shippingAddress->getHrxParcelTerminal();
        $parcel_terminal = $this->hrxCarrier->getTerminalAddress($terminal_id);
        return $parcel_terminal;
    }

    public function getReceiverCountry($order) {
        if (!$order) {
            return 'LT';
        }
        $shippingAddress = $order->getShippingAddress();
        $country = $shippingAddress->getCountryId();
        return $country ?? 'LT';
    }

    public function getTerminals($order = false) {
        $parcel_terminals = $this->hrxCarrier->getTerminals($this->getReceiverCountry($order)); //$this->getAddress()->getCountryId());
        return $parcel_terminals;
    }

    /**
     * Retrieve order model instance
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder() {
        return $this->coreRegistry->registry('current_order');
    }

    public function blockIsVisible() {
        if (isset($this->data['up_to_version'])) {
            if ($this->getMagentoVersion() >= $this->data['up_to_version']) {
                return false;
            }
        } 
        return true;
    }

    public function getMagentoVersion() {
        return $this->productMetadata->getVersion();
    }
    

}
