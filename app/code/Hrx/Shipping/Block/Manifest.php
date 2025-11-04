<?php

namespace Hrx\Shipping\Block;

use Hrx\Shipping\Model\Carrier;

class Manifest extends \Magento\Framework\View\Element\Template
{

    protected $_orderCollectionFactory;
    private $productMetadata;
    protected $hrxCarrier;

    public function __construct(
            \Magento\Framework\View\Element\Template\Context $context,
            \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
            \Magento\Framework\App\ProductMetadataInterface $productMetadata,
            Carrier $hrxCarrier
    ) {
        parent::__construct($context);
        $this->productMetadata = $productMetadata;
        $this->hrxCarrier = $hrxCarrier;
        $this->_orderCollectionFactory = $orderCollectionFactory;
    }

    public function getMagentoVersion() {
        return $this->productMetadata->getVersion();
    }

    public function getOrders() {
        $collection = $this->_orderCollectionFactory->create()->addFieldToFilter('shipping_method', array('like' => 'hrx_%'))->addFieldToFilter('state', array('neq' => 'canceled'))->setOrder('entity_id', 'DESC')->load();
        return $collection;
    }

    public function getOrderActions($order) {
        $buttons = [];
        if ($order->getStatus() == 'new') {
            $buttons[] = '<button class = "hrx-btn btn-sm single-action" data-action = "register" data-id = "' . $order->getOrderId() . '">'.__('Register').'</button> ';
        }
        if ($order->getStatus() == 'error') {
            $buttons[] = '<button class = "hrx-btn btn-sm single-action" data-action = "register" data-id = "' . $order->getOrderId() . '">'.__('Try register again').'</button> ';
        }
        if ($order->getStatus() == 'registered') {
            $buttons[] = '<button class = "hrx-btn btn-sm single-action" data-action = "ready" data-id = "' . $order->getOrderId() . '">'.__('Ready').'</button> ';
        }
        if ($order->getStatus() == 'ready') {
            //$buttons[] = '<button class = "hrx-btn btn-sm btn-outline single-action" data-action = "not_ready" data-id = "' . $order->getOrderId() . '">'.__('Unready').'</button> ';
        }
        if (!in_array($order->getStatus(),['new','error','deleted','canceled'])) {
            $buttons[] = '<button class = "hrx-btn btn-sm btn-outline single-action" data-action = "label" data-id = "' . $order->getOrderId() . '">'.__('Label').'</button> ';
            $buttons[] = '<button class = "hrx-btn btn-sm btn-outline single-action" data-action = "return_label" data-id = "' . $order->getOrderId() . '">'.__('Return label').'</button>';
        }

        return implode('', $buttons);
    }

    public function getOrderEdit($order) {
        if ($order->getStatus() != 'new' && $order->getStatus() != 'deleted' && $order->getStatus() != 'cancelled') {
            return '<a href = "'.$this->getUrl('hrx/order') .'?order_id='. $order->getOrderId().'" class = "hrx-preview-btn"></a> ';
        }
        return '<a href = "'.$this->getUrl('hrx/order') .'?order_id='. $order->getOrderId().'" class = "hrx-edit-btn"></a> ';
    }

    public function getTracking($order) {
        if (!$order->getTracking()){
            return '-';
        }
        if ($order->getTrackingUrl()) {
            return '<a href = "'.$order->getTrackingUrl().'" target = "_blank">'.$order->getTracking().'</a>';
        }
        return $order->getTracking();
    }

    public function getDeliveryType($order) {
        $shipping_method = $order->getShippingMethod();
        if ($shipping_method == 'hrx_parcel_terminal') {
            return 'Terminal';
        } else if ($shipping_method == 'hrx_courier') {
            return 'Courier';
        }
        return '-';
    }
    
    public function getTerminal($order) {
        $parcel_terminal = $this->hrxCarrier->getTerminalAddress($order->getHrxTerminalId());
        return $parcel_terminal;
    }

    public function getWarehouse($order) {
        $parcel_terminal = $this->hrxCarrier->getWarehouseAddress($order->getHrxWarehouseId());
        return $parcel_terminal;
    }

    public function getHrxOrder($order) {
        return $this->hrxCarrier->getHrxOrder($order);
    }
    
    public function getStatus($order) {
        $statuses = [
            'new' => 'New',
            'registered' => 'Registered',
            'ready' => 'Ready',
            'in_delivery' => 'In delivery',
            'delivered' => 'Delivered',
            'in_return' => 'In return',
            'returned' => 'Returned',
            'deleted' => 'Deleted',
            'cancelled' => 'Cancelled',
            'error' => 'Error',
        ];
        return $statuses[$order->getStatus()] ?? '-';
    }
    

}
