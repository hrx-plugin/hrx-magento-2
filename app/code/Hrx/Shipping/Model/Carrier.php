<?php

/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
// @codingStandardsIgnoreFile

namespace Hrx\Shipping\Model;

use Magento\Framework\Module\Dir;
use Magento\Framework\Xml\Security;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Carrier\AbstractCarrierOnline;
use Magento\Shipping\Model\Rate\Result;
use Magento\Shipping\Model\Tracking\Result as TrackingResult;

use Hrx\Shipping\Model\HrxOrderFactory;
use Hrx\Shipping\Model\HrxTerminalFactory;
use Hrx\Shipping\Model\HrxLocationFactory;
use Hrx\Shipping\Model\HrxWarehouseFactory;
use Hrx\Shipping\Model\Helper\HrxApi;

/**
 * Hrx shipping implementation
 *
 * @author Magento Core Team <core@magentocommerce.com>
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Carrier extends AbstractCarrierOnline implements \Magento\Shipping\Model\Carrier\CarrierInterface
{

    /**
     * Code of the carrier
     *
     * @var string
     */
    const CODE = 'hrx';


    /**
     * Code of the carrier
     *
     * @var string
     */
    protected $_code = self::CODE;

    /**
     * Rate request data
     *
     * @var RateRequest|null
     */
    protected $_request = null;

    /**
     * Rate result data
     *
     * @var Result|TrackingResult
     */
    protected $_result = null;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * @inheritdoc
     */
    protected $_debugReplacePrivateDataKeys = [
        'secret',
    ];

    protected $configWriter;

    /**
     * Session instance reference
     * 
     */
    protected $_checkoutSession;
    protected $variableFactory;

    protected $hrxWarehouseFactory;
    protected $hrxTerminalFactory;
    protected $hrxLocationFactory;
    protected $hrxOrderFactory;
    protected $hrxApi;
    protected $_resource;
    protected $orderRepository;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param Security $xmlSecurity
     * @param \Magento\Shipping\Model\Simplexml\ElementFactory $xmlElFactory
     * @param \Magento\Shipping\Model\Rate\ResultFactory $rateFactory
     * @param \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory
     * @param \Magento\Shipping\Model\Tracking\ResultFactory $trackFactory
     * @param \Magento\Shipping\Model\Tracking\Result\ErrorFactory $trackErrorFactory
     * @param \Magento\Shipping\Model\Tracking\Result\StatusFactory $trackStatusFactory
     * @param \Magento\Directory\Model\RegionFactory $regionFactory
     * @param \Magento\Directory\Model\CountryFactory $countryFactory
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param \Magento\Directory\Helper\Data $directoryData
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Module\Dir\Reader $configReader
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
            \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
            \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
            \Psr\Log\LoggerInterface $logger,
            Security $xmlSecurity,
            \Magento\Shipping\Model\Simplexml\ElementFactory $xmlElFactory,
            \Magento\Shipping\Model\Rate\ResultFactory $rateFactory,
            \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
            \Magento\Shipping\Model\Tracking\ResultFactory $trackFactory,
            \Magento\Shipping\Model\Tracking\Result\ErrorFactory $trackErrorFactory,
            \Magento\Shipping\Model\Tracking\Result\StatusFactory $trackStatusFactory,
            \Magento\Directory\Model\RegionFactory $regionFactory,
            \Magento\Directory\Model\CountryFactory $countryFactory,
            \Magento\Directory\Model\CurrencyFactory $currencyFactory,
            \Magento\Directory\Helper\Data $directoryData,
            \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
            \Magento\Store\Model\StoreManagerInterface $storeManager,
            \Magento\Framework\Module\Dir\Reader $configReader,
            \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
            \Magento\Framework\App\Config\Storage\WriterInterface $configWriter,
            \Magento\Checkout\Model\Session $checkoutSession,
            \Magento\Variable\Model\VariableFactory $variableFactory,
            HrxTerminalFactory $hrxTerminalFactory,
            HrxLocationFactory $hrxLocationFactory,
            HrxOrderFactory $hrxOrderFactory,
            HrxWarehouseFactory $hrxWarehouseFactory,
            HrxApi $hrxApi,
            \Magento\Framework\App\ResourceConnection $resource,
            \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
            array $data = []
    ) {
        $this->_checkoutSession = $checkoutSession;

        $this->_storeManager = $storeManager;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->variableFactory = $variableFactory;

        $this->hrxTerminalFactory = $hrxTerminalFactory;
        $this->hrxLocationFactory = $hrxLocationFactory;
        $this->hrxOrderFactory = $hrxOrderFactory;
        $this->hrxWarehouseFactory = $hrxWarehouseFactory;
        $this->hrxApi = $hrxApi;
        $this->orderRepository = $orderRepository;
        parent::__construct(
                $scopeConfig,
                $rateErrorFactory,
                $logger,
                $xmlSecurity,
                $xmlElFactory,
                $rateFactory,
                $rateMethodFactory,
                $trackFactory,
                $trackErrorFactory,
                $trackStatusFactory,
                $regionFactory,
                $countryFactory,
                $currencyFactory,
                $directoryData,
                $stockRegistry,
                $data
        );

        $this->hrxApi->init($this->getConfigData('secret'), $logger, $this->getConfigFlag('test_mode'));
        $this->_resource = $resource;
    }

    /**
     * Collect and get rates
     *
     * @param RateRequest $request
     * @return Result|bool|null
     */
    public function collectRates(RateRequest $request) {
        if (!$this->getConfigFlag('active')) {
            return false;
        }
        
        $countries = $this->getCode('country');
        $country_id = $this->_checkoutSession->getQuote()->getShippingAddress()->getCountryId();
        
        if (!$country_id) {
            $country_id = $request->getDestCountryId();
        }
        
        $maxWeight = $this->getConfigData('max_package_weight');
        if($request->getPackageWeight() > $maxWeight) {
            return false;
        }

        $amount = $this->getConfigData('price');
        $courier_amount = $this->getConfigData('courier_price');
        $ranges = (array)json_decode($this->getConfigData('ranges'));
        $courier_ranges = (array)json_decode($this->getConfigData('courier_ranges'));
        //terminal ranges
        if (is_array($ranges)) {
            foreach ($ranges as $range) {
                if (
                    in_array($country_id, $range->countries) && 
                    (float)$range->weight_from < $request->getPackageWeight() && 
                    $request->getPackageWeight() <= (float)$range->weight_to
                ){
                    $amount = (float)$range->price;
                    break;
                }
            }
        }
        //courier ranges
        if (is_array($courier_ranges)) {
            foreach ($ranges as $range) {
                if (
                    in_array($country_id, $range->countries) && 
                    (float)$range->weight_from < $request->getPackageWeight() && 
                    $request->getPackageWeight() <= (float)$range->weight_to
                ){
                    $courier_amount = (float)$range->price;
                    break;
                }
            }
        }

        $result = $this->_rateFactory->create();
        //$packageValue = $request->getBaseCurrency()->convert($request->getPackageValueWithDiscount(), $request->getPackageCurrency());
        $packageValue = $request->getPackageValueWithDiscount();

        $isFreeEnabled = $this->getConfigData('free_shipping_enable');
        
        //terminal method
        if ($country_id && $this->hasTerminal($country_id)) {
            $method = $this->_rateMethodFactory->create();

            $method->setCarrier('hrx');
            $method->setCarrierTitle($this->getConfigData('title'));

            $method->setMethod('parcel_terminal');
            $method->setMethodTitle(__('Parcel terminal'));
            
            $freeFrom = $this->getConfigData('free_shipping_from');
                
            if ($isFreeEnabled && $packageValue >= $freeFrom) {
                $amount = 0;
            }
            $method->setPrice($amount);
            $method->setCost($amount);
            $result->append($method);
        }
        //courier method
        if ($country_id && $this->hasLocation($country_id)) {
            $method = $this->_rateMethodFactory->create();

            $method->setCarrier('hrx');
            $method->setCarrierTitle($this->getConfigData('title'));

            $method->setMethod('courier');
            $method->setMethodTitle(__('Courier'));
            
            $freeFrom = $this->getConfigData('free_shipping_from');
                
            if ($isFreeEnabled && $packageValue >= $freeFrom) {
                $courier_amount = 0;
            }
            $method->setPrice($courier_amount);
            $method->setCost($courier_amount);
            $result->append($method);
        }
        
        return $result;
    }

    /**
     * Get version of rates request
     *
     * @return array
     */
    public function getVersionInfo() {
        return ['ServiceId' => 'crs', 'Major' => '10', 'Intermediate' => '0', 'Minor' => '0'];
    }

    /**
     * Get configuration data of carrier
     *
     * @param string $type
     * @param string $code
     * @return array|false
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function getCode($type, $code = '') {

        $codes = [
            'country' => [
                'AT' => __('Austria'),
                'BE' => __('Belgium'),
                'BG' => __('Bulgaria'),
                'HR' => __('Croatia'),
                'CZ' => __('Czech Republic'),
                'DK' => __('Denmark'),
                'EE' => __('Estonia'),
                'FI' => __('Finland'),
                'FR' => __('France'),
                'DE' => __('Germany'),
                'GR' => __('Greece'),
                'HU' => __('Hungary'),
                'IE' => __('Ireland'),
                'IT' => __('Italy'),
                'LV' => __('Latvia'),
                'LT' => __('Lithuania'),
                'NL' => __('Netherlands'),
                'PL' => __('Poland'),
                'PT' => __('Portugal'),
                'RO' => __('Romania'),
                'SK' => __('Slovakia'),
                'SI' => __('Slovenia'),
                'ES' => __('Spain'),
                'SE' => __('Sweden'),
            ],
            'tracking' => [
            ],
            'terminal' => [],
        ];
        if ($type == 'terminal') {
            $locations = [];
            
            $codes['terminal'] = $this->getTerminals();
        }

        if ($type == 'warehouse') {
            $warehouses = $this->getWarehouses();
            $codes['warehouse'] = [];
            foreach ($warehouses as $warehouse) {
                $codes['warehouse'][$warehouse['warehouse_id']] = $warehouse['name'];
            }
        }


        if (!isset($codes[$type])) {
            return false;
        } elseif ('' === $code) {
            return $codes[$type];
        }

        if (!isset($codes[$type][$code])) {
            return false;
        } else {
            return $codes[$type][$code];
        }
    }

    public function getTerminalAddress($terminal_id) {
        $hrxTerminalFactory = $this->hrxTerminalFactory->create();
        $terminal = $hrxTerminalFactory->load($terminal_id, 'terminal_id');
        if ($terminal && $terminal->getId()) {
            $parcel_terminal_address = $terminal->getAddress() . ', ' . $terminal->getCity() . ', ' . $terminal->getCountry();
            return $parcel_terminal_address;
        }
        return '-';
    }

    public function getTerminal($terminal_id) {
        $hrxTerminalFactory = $this->hrxTerminalFactory->create();
        $terminal = $hrxTerminalFactory->load($terminal_id, 'terminal_id');
        if ($terminal) {
            return $terminal;
        }
        return null;
    }

    public function getWarehouseAddress($warehouse_id) {
        $hrxWarehouseFactory = $this->hrxWarehouseFactory->create();
        $warehouse = $hrxWarehouseFactory->load($warehouse_id, 'warehouse_id');
        if ($warehouse) {
            $address = $warehouse->getName() . ', ' . $warehouse->getAddress() . ', ' . $warehouse->getCity() . ', ' . $warehouse->getCountry();
            return $address;
        }
        return '';
    }

    public function updateParcelTerminals() {
        $terminals = $this->hrxApi->getTerminals();
        $connection = $this->_resource->getConnection();
        $table = $connection->getTableName('hrx_terminals');
        $query = "UPDATE `" . $table . "` SET `active`= '0'";
        $connection->query($query);
        foreach ($terminals as $terminal) {
            $hrxTerminalFactory = $this->hrxTerminalFactory->create();
            $terminal_ = $hrxTerminalFactory->load($terminal['id'], 'terminal_id');
            $terminal_->setActive(1);
            $terminal_->setTerminalId($terminal['id']);
            $terminal_->setCountry($terminal['country']);
            $terminal_->setCity($terminal['city']);
            $terminal_->setPostcode($terminal['zip']);
            $terminal_->setAddress($terminal['address']);
            $terminal_->setLatitude($terminal['latitude']);
            $terminal_->setLongitude($terminal['longitude']);
            $terminal_->setMaxWidth($terminal['max_width_cm']);
            $terminal_->setMaxLength($terminal['max_length_cm']);
            $terminal_->setMaxHeight($terminal['max_height_cm']);
            $terminal_->setMaxWeight($terminal['max_weight_kg']);
            $terminal_->setMinWidth($terminal['min_width_cm']);
            $terminal_->setMinLength($terminal['min_length_cm']);
            $terminal_->setMinHeight($terminal['min_height_cm']);
            $terminal_->setMinWeight($terminal['min_weight_kg']);
            $terminal_->setPhonePrefix($terminal['recipient_phone_prefix']);
            $terminal_->setPhoneRegex($terminal['recipient_phone_regexp']);
            $terminal_->save();
        }
        
        $query = "DELETE FROM `" . $table . "` WHERE `active`= '0'";
        $connection->query($query);
    }

    public function updateLocations() {
        $terminals = $this->hrxApi->getLocations();
        $connection = $this->_resource->getConnection();
        $table = $connection->getTableName('hrx_locations');
        $query = "UPDATE `" . $table . "` SET `active`= '0'";
        $connection->query($query);
        foreach ($terminals as $terminal) {
            $hrxLocationFactory = $this->hrxLocationFactory->create();
            $terminal_ = $hrxLocationFactory->load($terminal['country'], 'country');
            $terminal_->setActive(1);
            $terminal_->setCountry($terminal['country']);
            $terminal_->setMaxWidth($terminal['max_width_cm']);
            $terminal_->setMaxLength($terminal['max_length_cm']);
            $terminal_->setMaxHeight($terminal['max_height_cm']);
            $terminal_->setMaxWeight($terminal['max_weight_kg']);
            $terminal_->setMinWidth($terminal['min_width_cm']);
            $terminal_->setMinLength($terminal['min_length_cm']);
            $terminal_->setMinHeight($terminal['min_height_cm']);
            $terminal_->setMinWeight($terminal['min_weight_kg']);
            $terminal_->setPhonePrefix($terminal['recipient_phone_prefix']);
            $terminal_->setPhoneRegex($terminal['recipient_phone_regexp']);
            $terminal_->save();
        }
        
        $query = "DELETE FROM `" . $table . "` WHERE `active`= '0'";
        $connection->query($query);
    }

    public function updateWarehouses() {
        $warehouses = $this->hrxApi->getWarehouses();
        $connection = $this->_resource->getConnection();
        $table = $connection->getTableName('hrx_warehouses');
        $query = "UPDATE `" . $table . "` SET `active`= '0'";
        $connection->query($query);
        foreach ($warehouses as $warehouse) {
            $hrxWarehouseFactory = $this->hrxWarehouseFactory->create();
            $warehouse_ = $hrxWarehouseFactory->load($warehouse['id'], 'warehouse_id');
            $warehouse_->setWarehouseId($warehouse['id']);
            $warehouse_->setName($warehouse['name']);
            $warehouse_->setActive(1);
            $warehouse_->setCountry($warehouse['country']);
            $warehouse_->setCity($warehouse['city']);
            $warehouse_->setPostcode($warehouse['zip']);
            $warehouse_->setAddress($warehouse['address']);
            $warehouse_->save();
        }
        $query = "DELETE FROM `" . $table . "` WHERE `active`= '0'";
        $connection->query($query);
        /*
        $default_warehouse_id = $this->getOption('warehouse_default', 0);
        $test_warehouse = $this->warehouses()->where('id', $default_warehouse_id)->first();
        if (!$test_warehouse) {
            $first_warehouse = $this->warehouses()->first();
            if ($first_warehouse) {
                $this->setOption('warehouse_default', $first_warehouse->id);
            }
        }
        */
    }

    public function getTerminals($countryCode = null) {
        $terminals = array();
        $countries = $this->getCode('country');
        if ($countryCode && !isset($countries[$countryCode])) {
            return $terminals;
        }

        $collection = $this->hrxTerminalFactory->create()->getCollection();
        if (count($collection) == 0) {
            $this->updateParcelTerminals();
        }

        if ($countryCode) {
            $collection = $this->hrxTerminalFactory->create()->getCollection()->addFieldToFilter('country', array('eq' => $countryCode));
        } else {
            $collection = $this->hrxTerminalFactory->create()->getCollection();
        }
        
		foreach($collection as $item){
			$terminals[] = $item->getData();
		}
        return $terminals;
    }

    public function hasTerminal($countryCode) {
        $collection = $this->hrxTerminalFactory->create()->getCollection();
        if (count($collection) == 0) {
            $this->updateParcelTerminals();
        }
        $collection = $this->hrxTerminalFactory->create()->getCollection()->addFieldToFilter('country', array('eq' => $countryCode));
        if (count($collection) > 0) {
            return true;
        }
		return false;
    }

    public function hasLocation($countryCode) {
        $collection = $this->hrxLocationFactory->create()->getCollection();
        if (count($collection) == 0) {
            $this->updateLocations();
        }
        $collection = $this->hrxLocationFactory->create()->getCollection()->addFieldToFilter('country', array('eq' => $countryCode));
        if (count($collection) > 0) {
            return true;
        }
		return false;
    }

    public function getLocation($countryCode) {
        $collection = $this->hrxLocationFactory->create()->getCollection();
        if (count($collection) == 0) {
            $this->updateLocations();
        }
        $item = $this->hrxLocationFactory->create()->getCollection()->addFieldToFilter('country', array('eq' => $countryCode))->getFirstItem();
        return $item;
    }

    public function getWarehouses() {
        $warehouses = array();
        $collection = $this->hrxWarehouseFactory->create()->getCollection();
        
        if (count($collection) == 0) {
            $this->updateWarehouses();
        }
		foreach($collection as $item){
			$warehouses[] = $item->getData();
		}
        return $warehouses;
    }

    /**
     * Get tracking
     *
     * @param string|string[] $trackings
     * @return Result|null
     */
    
    public function getTracking($trackings) {

        $result = $this->_trackFactory->create();
        /*
                    $tracking = $this->_trackStatusFactory->create();
                    $tracking->setCarrier($this->_code);
                    $tracking->setCarrierTitle($this->getConfigData('title'));
                    $tracking->setTracking($trackNum);
                    $tracking->addData($data);
                    $result->append($tracking);
        */
        return $result;
    }

    public function getTrackingInfo($tracking) {
        $trackingInfo = [];
        $order = $this->getHrxOrderByTracking($tracking);
        if ($order) {
            $trackingInfo['title'] = $order->getTracking();
            $trackingInfo['number'] = $order->getTrackingUrl();
        }
        return $trackingInfo;
    }

    /**
     * Receive tracking number and labels.
     *
     * @param $hrx_order
     * @return \Magento\Framework\DataObject
     */
    protected function _getShipmentLabels($hrx_order) {

        $result = new \Magento\Framework\DataObject();
        try {
            $sticker = $this->getLabel($hrx_order);
            if (isset($sticker['file_content'])) {
                $result->setShippingLabelContent($sticker['file_content']);
                $result->setTrackingNumber($hrx_order->getTracking());
            } else {
                $result->setErrors(sprintf(__('Labels not received for barcodes: %s'), implode(', ', $barcodes)));
            }
        } catch (\Exception $e) {
            $result->setErrors($e->getMessage());
        }
        return $result;
    }
    
    public function getLabels($barcodes) {

    }

    /**
     * Do shipment request to carrier web service, obtain Print Shipping Labels and process errors in response
     *
     * @param \Magento\Framework\DataObject $request
     * @return \Magento\Framework\DataObject
     * @throws \Exception
     */
    protected function _doShipmentRequest(\Magento\Framework\DataObject $request) {
        $barcodes = array();
        $this->_prepareShipmentRequest($request);
        $result = new \Magento\Framework\DataObject();

        try {
            $order = $request->getOrderShipment()->getOrder();
            $hrx_order = $this->getHrxOrder($order);

            if ($hrx_order->getTracking()) {
                return $this->_getShipmentLabels($hrx_order);
            } else {
                $result->setErrors(__('No tracking numbers received'));
            }
        } catch (HrxException $e) {
            $result->setErrors($e->getMessage());
        }
        return $result;
    }

    /**
     * @param array|object $trackingIds
     * @return string
     */
    private function getTrackingNumber($trackingIds) {
        return is_array($trackingIds) ? array_map(
                        function ($val) {
                            return $val->TrackingNumber;
                        },
                        $trackingIds
                ) : $trackingIds->TrackingNumber;
    }

    /**
     * For multi package shipments. Delete requested shipments if the current shipment
     * request is failed
     *
     * @param array $data
     * @return bool
     */
    public function rollBack($data) {
        
    }

    /**
     * Recursive replace sensitive fields in debug data by the mask
     * @param array $data
     * @return string
     */
    protected function filterDebugData($data) {
        foreach (array_keys($data) as $key) {
            if (is_array($data[$key])) {
                $data[$key] = $this->filterDebugData($data[$key]);
            } elseif (in_array($key, $this->_debugReplacePrivateDataKeys)) {
                $data[$key] = self::DEBUG_KEYS_MASK;
            }
        }
        return $data;
    }
    
    public function createHrxOrder($order) {
        try {
            $shippingAddress = $order->getShippingAddress();
            $model = $this->hrxOrderFactory->create();
            $data = [
                'order_id' => $order->getId(),
                'width' => $this->getConfigData('default_width'),
                'height' => $this->getConfigData('default_height'),
                'length' => $this->getConfigData('default_length'),
                'weight' => $this->getConfigData('default_weight'),
                'status' => 'new',
                'hrx_terminal_id' => $shippingAddress->getHrxParcelTerminal(),
                'hrx_warehouse_id' => $this->getConfigData('default_warehouse'),
            ];
            $model->setData($data);
            $model->save();
            return $model;
        } catch (\Exception $e) {
            
        }
        return false;
    }
    
    public function getHrxOrder($order) {
        try {
            $model = $this->hrxOrderFactory->create();
            $model->load($order->getId(), 'order_id');
            if ($model->getId()){
                return $model;
            }
            $model = $this->createHrxOrder($order);
            return $model;
        } catch (\Exception $e) {
            
        }
        return false;
    }
    
    public function getHrxOrderByTracking($tracking) {
        try {
            $model = $this->hrxOrderFactory->create();
            $model->load($tracking, 'tracking');
            if ($model->getId()){
                return $model;
            }
        } catch (\Exception $e) {
            
        }
        return false;
    }

    public function getAllowedMethods() {
        $arr = ['parcel_terminal'];
        return $arr;
    }

    public function getOrder($order_id) {
        return $this->orderRepository->get($order_id);
    }

    public function createHrxShipment($hrx_order, $order) {
        $terminal = $this->getTerminal($hrx_order->getHrxTerminalId());
        $location = $this->getLocation($order->getShippingAddress()->getCountryId());
        $this->hrxApi->createShipment($hrx_order, $order, $terminal, $location);
    }

    public function markAsReady($hrx_order, $mark_ready = true)
    {
        return $this->hrxApi->readyOrder($hrx_order, $mark_ready);
    }

    public function getLabel($hrx_order) {
        return $this->hrxApi->getLabel($hrx_order);
    }

    public function getHrxApiOrder($hrx_order) {
        return $this->hrxApi->getOrder($hrx_order);
    }

    public function getReturnLabel($hrx_order) {
        return $this->hrxApi->getReturnLabel($hrx_order);
    }

    public function cancelOrder($hrx_order) {
        return $this->hrxApi->cancelOrder($hrx_order);
    }

}
