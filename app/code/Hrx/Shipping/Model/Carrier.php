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
    protected $hrxOrderFactory;
    protected $hrxApi;
    protected $_resource;

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
            HrxOrderFactory $hrxOrderFactory,
            HrxWarehouseFactory $hrxWarehouseFactory,
            HrxApi $hrxApi,
            \Magento\Framework\App\ResourceConnection $resource,
            array $data = []
    ) {
        $this->_checkoutSession = $checkoutSession;

        $this->_storeManager = $storeManager;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->variableFactory = $variableFactory;

        $this->hrxTerminalFactory = $hrxTerminalFactory;
        $this->hrxOrderFactory = $hrxOrderFactory;
        $this->hrxWarehouseFactory = $hrxWarehouseFactory;
        $this->hrxApi = $hrxApi;
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

        $result = $this->_rateFactory->create();
        //$packageValue = $request->getBaseCurrency()->convert($request->getPackageValueWithDiscount(), $request->getPackageCurrency());
        $packageValue = $request->getPackageValueWithDiscount();

        $isFreeEnabled = $this->getConfigData('free_shipping_enable');
        
        $method = $this->_rateMethodFactory->create();

        $method->setCarrier('hrx');
        $method->setCarrierTitle($this->getConfigData('title'));

        $method->setMethod('parcel_terminal');
        $method->setMethodTitle(__('Parcel terminal'));
        $amount = $this->getConfigData('price');
        $freeFrom = $this->getConfigData('free_shipping_subtotal');
              
        if ($isFreeEnabled && $packageValue >= $freeFrom) {
            $amount = 0;
        }
        $method->setPrice($amount);
        $method->setCost($amount);

        $result->append($method);
        
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
                'EE' => __('Estonia'),
                'LV' => __('Latvia'),
                'LT' => __('Lithuania'),
                'PL' => __('Poland'),
                'FI' => __('Finland'),
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
        if ($terminal) {
            $parcel_terminal_address = $terminal->getAddress() . ', ' . $terminal->getCity() . ', ' . $terminal->getCountry();
            return $parcel_terminal_address;
        }
        return '';
    }

    private function updateParcelTerminals() {
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
        //$this->updateWarehouses();
        if ($countryCode) {
            $collection = $this->hrxTerminalFactory->create()->getCollection()->addFieldToFilter('country', array('eq' => $countryCode));
        } else {
            $collection = $this->hrxTerminalFactory->create()->getCollection();
        }
        
        if (count($collection) == 0) {
            $this->updateParcelTerminals();
        }
		foreach($collection as $item){
			$terminals[] = $item->getData();
		}
        return $terminals;
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

    protected function getReferenceNumber($order_number) {
        $order_number = (string) $order_number;
        $kaal = array(7, 3, 1);
        $sl = $st = strlen($order_number);
        $total = 0;
        while ($sl > 0 and substr($order_number, --$sl, 1) >= '0') {
            $total += substr($order_number, ($st - 1) - $sl, 1) * $kaal[($sl % 3)];
        }
        $kontrollnr = ((ceil(($total / 10)) * 10) - $total);
        return $order_number . $kontrollnr;
    }

    /**
     * Receive tracking number and labels.
     *
     * @param Array $barcodes
     * @return \Magento\Framework\DataObject
     */
    protected function _getShipmentLabels($barcodes) {

        $result = new \Magento\Framework\DataObject();
        try {
            $username = $this->getConfigData('account');
            $password = $this->getConfigData('password');

            $label = new Label();
            $label->setAuth($username, $password);
            $labels = $label->downloadLabels($barcodes, false, 'S');
            if ($labels) {
                $result->setShippingLabelContent($labels);
                $result->setTrackingNumber(is_array($barcodes) ? $barcodes[0] : $barcodes);
            } else {
                $result->setErrors(sprintf(__('Labels not received for barcodes: %s'), implode(', ', $barcodes)));
            }
        } catch (\Exception $e) {
            $result->setErrors($e->getMessage());
        }
        return $result;
    }
    
    public function getLabels($barcodes) {
        try {
            $username = $this->getConfigData('account');
            $password = $this->getConfigData('password');

            $label = new Label();
            $label->setAuth($username, $password);
            $combine = $this->getConfigData('combine_labels');
            $labels = $label->downloadLabels($barcodes, $combine, 'I');
            if ($labels) {
                
            } else {
                
            }
        } catch (\Exception $e) {
            
        }
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
            $username = $this->getConfigData('account');
            $password = $this->getConfigData('password');

            $name = $this->getConfigData('cod_company');
            $phone = $this->getConfigData('company_phone');
            $street = $this->getConfigData('company_address');
            $postcode = $this->getConfigData('company_postcode');
            $city = $this->getConfigData('company_city');
            $country = $this->getConfigData('company_countrycode');
            $bank_account = $this->getConfigData('cod_bank_account');

            $payment_method = $order->getPayment()->getMethodInstance()->getCode();
            $is_cod = $payment_method == 'msp_cashondelivery';

            $send_method_name = trim($request->getShippingMethod());
            $pickup_method = $this->getConfigData('pickup');
            
            $send_method = 'c';
            if (strtolower($send_method_name) == 'parcel_terminal') {
                $send_method = 'pt';
            } else if (strtolower($send_method_name) == 'courier_plus') {
                $send_method = 'cp';
            }
            
            $service = $this->shipping_helper->getShippingService($this, $send_method, $order);
            
            //in case cannot get correct service
            if ($service === false || is_array($service)) {
                switch ($pickup_method . ' ' . $send_method_name) {
                    case 'COURIER parcel_terminal':
                        $service = "PU";
                        break;
                    case 'COURIER COURIER':
                        $service = "QH";
                        break;
                    case 'parcel_terminal COURIER':
                        $service = "PK";
                        break;
                    case 'parcel_terminal parcel_terminal':
                        $service = "PA";
                        break;
                    default:
                        $service = "";
                        break;
                }
            }



            $shipment = new Shipment();
            /*
              $shipment
              ->setComment('Test comment')
              ->setShowReturnCodeEmail(true);
             */
            $shipmentHeader = new ShipmentHeader();
            $shipmentHeader
                    ->setSenderCd($username)
                    ->setFileId(date('Ymdhis'));
            $shipment->setShipmentHeader($shipmentHeader);

            $package = new Package();
            $package
                    ->setId($order->getId())
                    ->setService($service);
            
            $additionalServices = [];
            if ($service == "PA" || $service == "PU") {
                $additionalServices[] = (new AdditionalService())->setServiceCode('ST');
                if ($is_cod) {
                    $additionalServices[] = (new AdditionalService())->setServiceCode('BP');
                }
            }
            
            $_orderServices = json_decode($order->getHrxServices(), true);
            if (isset($_orderServices['services']) && is_array($_orderServices['services'])) {
                foreach ($_orderServices['services'] as $_service) {
                    $additionalServices[] = (new AdditionalService())->setServiceCode($_service);
                }
            }
            
            $package->setAdditionalServices($additionalServices);

            $measures = new Measures();
            $measures
                    ->setWeight($request->getPackageWeight());
            /*
              ->setVolume(9)
              ->setHeight(2)
              ->setWidth(3); */
            $package->setMeasures($measures);

            //set COD
            if ($is_cod) {
                $cod = new Cod();
                $cod
                        ->setAmount(round($request->getOrderShipment()->getOrder()->getGrandTotal(), 2))
                        ->setBankAccount($bank_account)
                        ->setReceiverName($name)
                        ->setReferenceNumber($this->getReferenceNumber($order->getId()));
                $package->setCod($cod);
            }
            // Receiver contact data
            $receiverContact = new Contact();
            $address = new Address();
            $address
                    ->setCountry($request->getRecipientAddressCountryCode())
                    ->setPostcode($request->getRecipientAddressPostalCode())
                    ->setDeliverypoint($request->getRecipientAddressCity())
                    ->setStreet($request->getRecipientAddressStreet1());
            if ($send_method_name === 'parcel_terminal') {
                $address->setOffloadPostcode($order->getShippingAddress()->getHrxParcelTerminal());
            }

            $receiverContact
                    ->setAddress($address)
                    ->setMobile($request->getRecipientContactPhoneNumber())
                    ->setPersonName($request->getRecipientContactPersonName());
            $package->setReceiverContact($receiverContact);

            // Sender contact data
            $sender_address = new Address();
            $sender_address
                    ->setCountry($country)
                    ->setPostcode($postcode)
                    ->setDeliverypoint($city)
                    ->setStreet($street);
            $senderContact = new Contact();
            $senderContact
                    ->setAddress($sender_address)
                    ->setMobile($phone)
                    ->setPersonName($name);
            $package->setSenderContact($senderContact);

            // Simulate multi-package request.
            $shipment->setPackages([$package]);

            //set auth data
            $shipment->setAuth($username, $password);

            $shipment_result = $shipment->registerShipment();
            if (isset($shipment_result['barcodes'])) {
                foreach ($shipment_result['barcodes'] as $_barcode) {
                    $this->createLabelHistory($order, $_barcode, $service);
                }
                return $this->_getShipmentLabels($shipment_result['barcodes']);
            } else {
                $result->setErrors(__('No saved barcodes received'));
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
            $model = $this->labelhistoryFactory->create();
            $data = [
                'shop_order_id' => $order->getId(),
            ];
            $model->setData($data);
            $model->save();
            return true;
        } catch (\Exception $e) {
            
        }
        return false;
    }

    public function getAllowedMethods() {
        $arr = ['parcel_terminal'];
        return $arr;
    }

}
