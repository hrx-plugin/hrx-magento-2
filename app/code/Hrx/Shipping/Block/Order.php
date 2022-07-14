<?php

namespace Hrx\Shipping\Block;

use Hrx\Shipping\Model\Carrier;

class Order extends \Magento\Framework\View\Element\Template
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
        $collection = $this->_orderCollectionFactory->create()->addFieldToFilter('shipping_method', array('like' => 'hrx_%'))->addFieldToFilter('state', array('neq' => 'canceled'))->load();
        return $collection;
    }

    public function getOrderTrackings($order) {
        if ($this->getMagentoVersion() < '2.3.0') {
            $old_version = true;
        } else {
            $old_version = false;
        }
        $barcode = '';
        foreach ($order->getShipmentsCollection() as $shipment) {
            foreach ($shipment->getAllTracks() as $tracknum) {
                $barcode .= '<a href = "'.$this->getUrl('hrx/orders/printlabels' . ($old_version ? 'ov' : '')).'?barcode='.$tracknum->getNumber().'" target = "_blank">'.$tracknum->getNumber() . '</a> ';
            }
        }
        if (!$barcode) {
            return '-';
        }
        return $barcode;
    }
    
    public function getTerminal($order) {
        $shippingAddress = $order->getShippingAddress();
        $terminal_id = $shippingAddress->getHrxParcelTerminal();
        $parcel_terminal = $this->hrxCarrier->getTerminalAddress($terminal_id);
        return $parcel_terminal;
    }
    

}
