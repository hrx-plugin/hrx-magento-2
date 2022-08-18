<?php

namespace Hrx\Shipping\Block;

use Hrx\Shipping\Model\Carrier;
use Hrx\Shipping\Model\HrxOrderFactory;

class Order extends \Magento\Framework\View\Element\Template
{

    protected $_orderCollectionFactory;
    protected $orderRepository;
    private $productMetadata;
    protected $hrxCarrier;
    protected $order = false;
    protected $hrx_order = false;
    protected $request;
    protected $hrxOrderFactory;
    protected $formKey;

    public function __construct(
            \Magento\Framework\View\Element\Template\Context $context,
            \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
            \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
            \Magento\Framework\App\ProductMetadataInterface $productMetadata,
            Carrier $hrxCarrier,
            \Magento\Framework\App\Request\Http $request,
            HrxOrderFactory $hrxOrderFactory,
            \Magento\Framework\Data\Form\FormKey $formKey
    ) {
        parent::__construct($context);
        $this->productMetadata = $productMetadata;
        $this->hrxCarrier = $hrxCarrier;
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->orderRepository = $orderRepository;
        $this->hrxOrderFactory = $hrxOrderFactory;
        $this->request = $request;
        $this->formKey = $formKey;
        
        $this->getHrxOrder();
    }

    public function getOrder()
    {
        try {
            if ($this->order) {
                return $this->order;
            }
            $order_id = $this->request->getParam('order_id');
            $order = $this->orderRepository->get($order_id);
            
            if ($order) {
                $this->order = $order;
                return $this->order;
            }
        } catch (\Throwable $e) {
            
        }
        return false;
    }

    public function getOrderId()
    {
        $order = $this->getOrder();
        return $order->getId() ?? 0;
    }

    public function isTerminal() {
        $shipping_method = $this->getOrder()->getShippingMethod();
        if ($shipping_method == 'hrx_parcel_terminal') {
            return true;
        } 
        return false;
    }

    public function getOrderNumber()
    {
        $order = $this->getOrder();
        return $order->getIncrementId();
    }

    public function getHrxOrder()
    {
        $order = $this->getOrder();
        try {
            if ($this->hrx_order) {
                return $this->hrx_order;
            }
            $hrx_order = $this->hrxCarrier->getHrxOrder($order);

            
            if ($hrx_order) {
                $this->hrx_order = $hrx_order;
                return $this->hrx_order;
            }
        } catch (\Throwable $e) {
            
        }
        return false;
    }

    public function getReceiverCountry() {
        $order = $this->getOrder();
        if (!$order) {
            return 'LT';
        }
        $shippingAddress = $order->getShippingAddress();
        $country = $shippingAddress->getCountryId();
        return $country ?? 'LT';
    }

    public function terminalOptions()
    {
        $order = $this->getOrder();

        $terminal_options = "";
        $current_terminal = $this->getTerminalId();
        $parcel_terminals = $this->hrxCarrier->getTerminals($this->getReceiverCountry());
        foreach ($parcel_terminals as $loc) {
            $key = $loc['terminal_id'];
            if (!isset($grouped_options[(string) $loc['city']])) {
                $grouped_options[(string) $loc['city']] = array();
            }
            $grouped_options[(string) $loc['city']][(string) $key] = $loc;
        }
        ksort($grouped_options);
        foreach ($grouped_options as $city => $locs) {
            $terminal_options .= '<optgroup label = "' . $city . '">';
            foreach ($locs as $key => $loc) {
                $terminal_options .= '<option value = "' . $key . '" ' . ($key == $current_terminal ? 'selected' : '') . '>' . $loc['address'] . ', ' . $loc['city'] . '</option>';
            }
            $terminal_options .= '</optgroup>';
        }
        return $terminal_options;
    } 

    public function warehouseOptions()
    {
        $current_warehouse = $this->hrx_order->getHrxWarehouseId();
        $warehouse_options = "";
        $warehouses = $this->hrxCarrier->getWarehouses();
        foreach ($warehouses as $warehouse) {
            $warehouse_options .= '<option value = "' . $warehouse['warehouse_id'] . '" ' . ($warehouse['warehouse_id'] == $current_warehouse ? 'selected' : '') . '>' . $warehouse['name'] . '</option>';
        }
        return $warehouse_options;
    } 

    public function getTerminalId() {
        $order = $this->getOrder();
        if ($order) {
            $shippingAddress = $order->getShippingAddress();
            $terminal_id = $shippingAddress->getHrxParcelTerminal();
            return $terminal_id;
        }
        return null;
    }
    
    public function readOnly() {
        return $this->hrx_order->getStatus() != 'new';
    }

    public function inputDisabled() {
        if ($this->readOnly()) {
            echo 'disabled="disabled"';
        }
    }

    public function getFormKey()
    {
         return $this->formKey->getFormKey();
    }

}
