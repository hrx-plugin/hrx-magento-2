<?php

namespace Hrx\Shipping\Block;

use Hrx\Shipping\Model\Carrier;
use Hrx\Shipping\Model\LabelHistoryFactory;

class Manifest extends \Magento\Framework\View\Element\Template
{

    protected $_orderCollectionFactory;
    private $productMetadata;
    protected $hrxCarrier;
    protected $labelhistoryFactory;

    public function __construct(
            \Magento\Framework\View\Element\Template\Context $context,
            \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
            \Magento\Framework\App\ProductMetadataInterface $productMetadata,
            Carrier $hrxCarrier,
            LabelHistoryFactory $labelhistoryFactory
    ) {
        parent::__construct($context);
        $this->productMetadata = $productMetadata;
        $this->hrxCarrier = $hrxCarrier;
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->labelhistoryFactory = $labelhistoryFactory;
    }

    public function getMagentoVersion() {
        return $this->productMetadata->getVersion();
    }

    public function getOrders() {
        $collection = $this->_orderCollectionFactory->create()->addFieldToFilter('shipping_method', array('like' => 'hrx_%'))->addFieldToFilter('state', array('neq' => 'canceled'))->load();
        return $collection;
    }
    
    public function getShippingMethod($order) {
        $order_shipping_method = strtolower($order->getData('shipping_method'));
        if ($order_shipping_method === 'hrx_courier') {
            return __('Courier');
        }
        if ($order_shipping_method === 'hrx_courier_plus') {
            return __('Courier Plus');
        }
        if ($order_shipping_method === 'hrx_parcel_terminal') {
            return __('Parcel terminal') . ': '. $this->getTerminal($order);
        }
        return '-';
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
                $barcode .= '<a href = "'.$this->getUrl('hrx/omnivamanifest/printlabels' . ($old_version ? 'ov' : '')).'?barcode='.$tracknum->getNumber().'" target = "_blank">'.$tracknum->getNumber() . '</a> ';
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
    
    public function getOrderHistory($order) {
        if ($this->getMagentoVersion() < '2.3.0') {
            $old_version = true;
        } else {
            $old_version = false;
        }
        $history = '';
        try {
            $history_items = $this->labelhistoryFactory->create()->getCollection() ->addFieldToSelect('*');
            $history_items->addFieldToFilter('order_id', array('eq' => $order->getId()));
            foreach ($history_items as $item) {
                $history .= '<a href = "'.$this->getUrl('hrx/omnivamanifest/printlabels' . ($old_version ? 'ov' : '')).'?barcode='.$item->getLabelBarcode().'" target = "_blank">' . $item->getLabelBarcode() . '</a> ';
                if ($item->getServices()) {
                    $history .= $item->getServices() . ' ';
                }
                $history .= $item->getCreatedAt() . '<br/>';
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        if (!$history) {
            return '-';
        }
        return $history;
    }

}
