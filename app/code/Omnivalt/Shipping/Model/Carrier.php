<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Omnivalt\Shipping\Model;

use Magento\Framework\Module\Dir;
use Magento\Framework\Xml\Security;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Carrier\AbstractCarrierOnline;
use Magento\Shipping\Model\Rate\Result;
use Magento\Shipping\Model\Tracking\Result as TrackingResult;

/**
 * Omnivalt shipping implementation
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
    const CODE = 'omnivalt';

    /**
     * Purpose of rate request
     *
     * @var string
     */
    const RATE_REQUEST_GENERAL = 'general';

    /**
     * Purpose of rate request
     *
     * @var string
     */
    const RATE_REQUEST_SMARTPOST = 'SMART_POST';

    /**
     * Code of the carrier
     *
     * @var string
     */
    protected $_code = self::CODE;

    /**
     * Types of rates, order is important
     *
     * @var array
     */
    protected $_ratesOrder = [
        'RATED_ACCOUNT_PACKAGE',
        'PAYOR_ACCOUNT_PACKAGE',
        'RATED_ACCOUNT_SHIPMENT',
        'PAYOR_ACCOUNT_SHIPMENT',
        'RATED_LIST_PACKAGE',
        'PAYOR_LIST_PACKAGE',
        'RATED_LIST_SHIPMENT',
        'PAYOR_LIST_SHIPMENT',
    ];

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
     * Path to wsdl file of rate service
     *
     * @var string
     */
    protected $_rateServiceWsdl;

    /**
     * Path to wsdl file of ship service
     *
     * @var string
     */
    protected $_shipServiceWsdl = null;

    /**
     * Path to wsdl file of track service
     *
     * @var string
     */
    protected $_trackServiceWsdl = null;

    /**
     * Path to locations xml
     *
     * @var string
     */
    protected $_locationFile;

    /**
     * Container types that could be customized for Omnivalt carrier
     *
     * @var string[]
     */
    protected $_customizableContainerTypes = ['YOUR_PACKAGING'];

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
        'Account', 'Password'
    ];

    /**
     * Version of tracking service
     * @var int
     */
    private static $trackServiceVersion = 10;

    /**
     * List of TrackReply errors
     * @var array
     */
    private static $trackingErrors = ['FAILURE', 'ERROR'];



    /**
     * @var \Magento\Framework\Xml\Parser
    */
    private $XMLparser;
    
    protected $configWriter;
    /**
     * Session instance reference
     * 
     */
    protected $_checkoutSession;

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
        \Magento\Framework\Xml\Parser $parser,
        \Magento\Framework\App\Config\Storage\WriterInterface $configWriter,
        \Magento\Checkout\Model\Session $checkoutSession,
        array $data = []
    ) {
        $this->_checkoutSession = $checkoutSession;

        $this->_storeManager = $storeManager;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->XMLparser = $parser;
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

        $this->_locationFile = $configReader->getModuleDir(Dir::MODULE_ETC_DIR, 'Omnivalt_Shipping') . '/location.xml';
        if (!$this->getConfigData('location_update') || ($this->getConfigData('location_update') + 3600 * 24) < time() || !file_exists($this->_locationFile)) {
          $url  = 'https://www.omniva.ee/locations.xml';
          $fp   = fopen($this->_locationFile, "w");
          $curl = curl_init();
          curl_setopt($curl, CURLOPT_URL, $url);
          curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
          curl_setopt($curl, CURLOPT_HEADER, false);
          curl_setopt($curl, CURLOPT_FILE, $fp);
          curl_setopt($curl, CURLOPT_TIMEOUT, 60);
          curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
          curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
          $data = curl_exec($curl);
          curl_close($curl);
          fclose($fp);
          if ($data !== false) {
            $this->configWriter = $configWriter;
            $this->configWriter->save("carriers/omnivalt/location_update", time());
          }
        }
      }



    /**
     * Collect and get rates
     *
     * @param RateRequest $request
     * @return Result|bool|null
     */
    public function collectRates(RateRequest $request)
    {
        if (!$this->getConfigFlag('active')) {
            return false;
        }
        
        $result = $this->_rateFactory->create();
        $packageValue = $request->getBaseCurrency()->convert($request->getPackageValueWithDiscount(), $request->getPackageCurrency());
        $packageValue = $request->getPackageValueWithDiscount(); 
        $this->_updateFreeMethodQuote($request);
        $free = ($this->getConfigData('free_shipping_enable') && $packageValue >= $this->getConfigData('free_shipping_subtotal'));
        $allowedMethods = explode(',', $this->getConfigData('allowed_methods'));
        foreach ($allowedMethods as $allowedMethod){
            $method = $this->_rateMethodFactory->create();
     
            $method->setCarrier('omnivalt');
            $method->setCarrierTitle($this->getConfigData('title'));
     
            $method->setMethod($allowedMethod);
            $method->setMethodTitle($this->getCode('method', $allowedMethod));
            $amount = $this->getConfigData('price');

            $country_id =  $this->_checkoutSession->getQuote()
            ->getShippingAddress()
            ->getCountryId();
            
            if ($allowedMethod == "COURIER") {
              switch($country_id) {
                case 'LV':
                    $amount = $this->getConfigData('priceLV_C');
                    break;
                case 'EE':
                    $amount = $this->getConfigData('priceEE_C');
                    break;
                default:
                    $amount = $this->getConfigData('price');
              }
            }
            if ($allowedMethod == "PARCEL_TERMINAL") {
              switch($country_id) {
                case 'LV':
                    $amount = $this->getConfigData('priceLV_pt');
                    break;
                case 'EE':
                    $amount = $this->getConfigData('priceEE_pt');
                    break;
                default:
                    $amount = $this->getConfigData('price2');
              }
            }
            if ($free)
              $amount = 0;

            $method->setPrice($amount);
            $method->setCost($amount);
        
            $result->append($method);
        }
        return $result;
    }

    /**
     * Prepare and set request to this instance
     *
     * @param RateRequest $request
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function setRequest(RateRequest $request)
    {
        $this->_request = $request;

        $r = new \Magento\Framework\DataObject();

        if ($request->getLimitMethod()) {
            $r->setService($request->getLimitMethod());
        }

        if ($request->getOmnivaltAccount()) {
            $account = $request->getOmnivaltAccount();
        } else {
            $account = $this->getConfigData('account');
        }
        $r->setAccount($account);

        if ($request->getOmnivaltDropoff()) {
            $dropoff = $request->getOmnivaltDropoff();
        } else {
            $dropoff = $this->getConfigData('dropoff');
        }
        $r->setDropoffType($dropoff);

        if ($request->getOmnivaltPackaging()) {
            $packaging = $request->getOmnivaltPackaging();
        } else {
            $packaging = $this->getConfigData('packaging');
        }
        $r->setPackaging($packaging);

        if ($request->getOrigCountry()) {
            $origCountry = $request->getOrigCountry();
        } else {
            $origCountry = $this->_scopeConfig->getValue(
                \Magento\Sales\Model\Order\Shipment::XML_PATH_STORE_COUNTRY_ID,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $request->getStoreId()
            );
        }
        $r->setOrigCountry($this->_countryFactory->create()->load($origCountry)->getData('iso2_code'));

        if ($request->getOrigPostcode()) {
            $r->setOrigPostal($request->getOrigPostcode());
        } else {
            $r->setOrigPostal(
                $this->_scopeConfig->getValue(
                    \Magento\Sales\Model\Order\Shipment::XML_PATH_STORE_ZIP,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $request->getStoreId()
                )
            );
        }

        if ($request->getDestCountryId()) {
            $destCountry = $request->getDestCountryId();
        } else {
            $destCountry = self::USA_COUNTRY_ID;
        }
        $r->setDestCountry($this->_countryFactory->create()->load($destCountry)->getData('iso2_code'));

        if ($request->getDestPostcode()) {
            $r->setDestPostal($request->getDestPostcode());
        } else {
        }

        if ($request->getDestCity()) {
            $r->setDestCity($request->getDestCity());
        }

        $weight = $this->getTotalNumOfBoxes($request->getPackageWeight());
        $r->setWeight($weight);
        if ($request->getFreeMethodWeight() != $request->getPackageWeight()) {
            $r->setFreeMethodWeight($request->getFreeMethodWeight());
        }

        $r->setValue($request->getPackagePhysicalValue());
        $r->setValueWithDiscount($request->getPackageValueWithDiscount());

        $r->setMeterNumber($this->getConfigData('meter_number'));
        $r->setKey($this->getConfigData('key'));
        $r->setPassword($this->getConfigData('password'));

        $r->setIsReturn($request->getIsReturn());

        $r->setBaseSubtotalInclTax($request->getBaseSubtotalInclTax());

        $this->setRawRequest($r);

        return $this;
    }

    /**
     * Get result of request
     *
     * @return Result|TrackingResult
     */
    public function getResult()
    {
        if (!$this->_result) {
            $this->_result = $this->_rateFactory->create();
        }
        return $this->_result;
    }

    /**
     * Get version of rates request
     *
     * @return array
     */
    public function getVersionInfo()
    {
        return ['ServiceId' => 'crs', 'Major' => '10', 'Intermediate' => '0', 'Minor' => '0'];
    }

    /**
     * Set free method request
     *
     * @param string $freeMethod
     * @return void
     */
    protected function _setFreeMethodRequest($freeMethod)
    {
        $this->_rawRequest->setFreeMethodRequest(true);
        $freeWeight = $this->getTotalNumOfBoxes($this->_rawRequest->getFreeMethodWeight());
        $this->_rawRequest->setWeight($freeWeight);
        $this->_rawRequest->setService($freeMethod);
    }

    /**
     * Prepare shipping rate result based on response
     *
     * @param mixed $response
     * @return Result
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _parseXmlResponse($response)
    {
        $costArr = [];
        $priceArr = [];

        if (strlen(trim($response)) > 0) {
            $xml = $this->parseXml($response, 'Magento\Shipping\Model\Simplexml\Element');
            if (is_object($xml)) {
                if (is_object($xml->Error) && is_object($xml->Error->Message)) {
                    $errorTitle = (string)$xml->Error->Message;
                } elseif (is_object($xml->SoftError) && is_object($xml->SoftError->Message)) {
                    $errorTitle = (string)$xml->SoftError->Message;
                } else {
                    $errorTitle = 'Sorry, something went wrong. Please try again or contact us and we\'ll try to help.';
                }

                $allowedMethods = explode(",", $this->getConfigData('allowed_methods'));

                foreach ($xml->Entry as $entry) {
                    if (in_array((string)$entry->Service, $allowedMethods)) {
                        $costArr[(string)$entry->Service] = (string)$entry
                            ->EstimatedCharges
                            ->DiscountedCharges
                            ->NetCharge;
                        $priceArr[(string)$entry->Service] = $this->getMethodPrice(
                            (string)$entry->EstimatedCharges->DiscountedCharges->NetCharge,
                            (string)$entry->Service
                        );
                    }
                }

                asort($priceArr);
            } else {
                $errorTitle = 'Response is in the wrong format.';
            }
        } else {
            $errorTitle = 'For some reason we can\'t retrieve tracking info right now.';
        }

        $result = $this->_rateFactory->create();
        if (empty($priceArr)) {
            $error = $this->_rateErrorFactory->create();
            $error->setCarrier('omnivalt');
            $error->setCarrierTitle($this->getConfigData('title'));
            $error->setErrorMessage($this->getConfigData('specificerrmsg'));
            $result->append($error);
        } else {
            foreach ($priceArr as $method => $price) {
                $rate = $this->_rateMethodFactory->create();
                $rate->setCarrier('omnivalt');
                $rate->setCarrierTitle($this->getConfigData('title'));
                $rate->setMethod($method);
                $rate->setMethodTitle($this->getCode('method', $method));
                $rate->setCost($costArr[$method]);
                $rate->setPrice($price);
                $result->append($rate);
            }
        }

        return $result;
    }

    /**
     * Get configuration data of carrier
     *
     * @param string $type
     * @param string $code
     * @return array|false
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function getCode($type, $code = '')
    {

        $codes = [
            'method' => [
                'COURIER' => __('Courier'),
                'PARCEL_TERMINAL' => __('Parcel terminal'),
            ],
            'country' => [
                'EE' => __('Estonia'),
                'LV' => __('Latvia'),
                'LT' => __('Lithuania')
            ],
            'tracking' => [
                'PACKET_EVENT_IPS_C' => __("Shipment from country of departure"),
                'PACKET_EVENT_FROM_CONTAINER' => __("Arrival to post office"),
                'PACKET_EVENT_IPS_D' => __("Arrival to destination country"),
                'PACKET_EVENT_SAVED' => __("Saving"),
                'PACKET_EVENT_DELIVERY_CANCELLED' => __("Cancelling of delivery"),
                'PACKET_EVENT_IN_POSTOFFICE' => __("Arrival to Omniva"),
                'PACKET_EVENT_IPS_E' => __("Customs clearance"),
                'PACKET_EVENT_DELIVERED' => __("Delivery"),
                'PACKET_EVENT_FROM_WAYBILL_LIST' => __("Arrival to post office"),
                'PACKET_EVENT_IPS_A' => __("Acceptance of packet from client"),
                'PACKET_EVENT_IPS_H' => __("Delivery attempt"),
                'PACKET_EVENT_DELIVERING_TRY' => __("Delivery attempt"),
                'PACKET_EVENT_DELIVERY_CALL' => __("Preliminary calling"),
                'PACKET_EVENT_IPS_G' => __("Arrival to destination post office"),
                'PACKET_EVENT_ON_ROUTE_LIST' => __("Dispatching"),
                'PACKET_EVENT_IN_CONTAINER' => __("Dispatching"),
                'PACKET_EVENT_PICKED_UP_WITH_SCAN' => __("Acceptance of packet from client"),
                'PACKET_EVENT_RETURN' => __("Returning"),
                'PACKET_EVENT_SEND_REC_SMS_NOTIF' => __("SMS to receiver"),
                'PACKET_EVENT_ARRIVED_EXCESS' => __("Arrival to post office"),
                'PACKET_EVENT_IPS_I' => __("Delivery"),
                'PACKET_EVENT_ON_DELIVERY_LIST' => __("Handover to courier"),
                'PACKET_EVENT_PICKED_UP_QUANTITATIVELY' => __("Acceptance of packet from client"),
                'PACKET_EVENT_SEND_REC_EMAIL_NOTIF' => __("E-MAIL to receiver"),
                'PACKET_EVENT_FROM_DELIVERY_LIST' => __("Arrival to post office"),
                'PACKET_EVENT_OPENING_CONTAINER' => __("Arrival to post office"),
                'PACKET_EVENT_REDIRECTION' => __("Redirection"),
                'PACKET_EVENT_IN_DEST_POSTOFFICE' => __("Arrival to receiver's post office"),
                'PACKET_EVENT_STORING' => __("Storing"),
                'PACKET_EVENT_IPS_EDD' => __("Item into sorting centre"),
                'PACKET_EVENT_IPS_EDC' => __("Item returned from customs"),
                'PACKET_EVENT_IPS_EDB' => __("Item presented to customs"),
                'PACKET_EVENT_IPS_EDA' => __("Held at inward OE"),
                'PACKET_STATE_BEING_TRANSPORTED' => __("Being transported"),
                'PACKET_STATE_CANCELLED' => __("Cancelled"),
                'PACKET_STATE_CONFIRMED' => __("Confirmed"),
                'PACKET_STATE_DELETED' => __("Deleted"),
                'PACKET_STATE_DELIVERED' => __("Delivered"),
                'PACKET_STATE_DELIVERED_POSTOFFICE' => __("Arrived at post office"),
                'PACKET_STATE_HANDED_OVER_TO_COURIER' => __("Transmitted to courier"),
                'PACKET_STATE_HANDED_OVER_TO_PO' => __("Re-addressed to post office"),
                'PACKET_STATE_IN_CONTAINER' => __("In container"),
                'PACKET_STATE_IN_WAREHOUSE' => __("At warehouse"),
                'PACKET_STATE_ON_COURIER' => __("At delivery"),
                'PACKET_STATE_ON_HANDOVER_LIST' => __("In transition sheet"),
                'PACKET_STATE_ON_HOLD' => __("Waiting"),
                'PACKET_STATE_REGISTERED' => __("Registered"),
                'PACKET_STATE_SAVED' => __("Saved"),
                'PACKET_STATE_SORTED' => __("Sorted"),
                'PACKET_STATE_UNCONFIRMED' => __("Unconfirmed"),
                'PACKET_STATE_UNCONFIRMED_NO_TARRIF' => __("Unconfirmed (No tariff)"),
                'PACKET_STATE_WAITING_COURIER' => __("Awaiting collection"),
                'PACKET_STATE_WAITING_TRANSPORT' => __("In delivery list"),
                'PACKET_STATE_WAITING_UNARRIVED' => __("Waiting, hasn't arrived"),
                'PACKET_STATE_WRITTEN_OFF' => __("Written off"),
            ],
            'terminal' => [],
        ];

        $locationsXMLArray = $this->XMLparser->load($this->_locationFile)->xmlToArray();
        $locations = array();
        foreach($locationsXMLArray['LOCATIONS']['_value']['LOCATION'] as $loc_data ){
            $locations[$loc_data['ZIP']] = array('name' => $loc_data['NAME'], 'country' => $loc_data['A0_NAME']);
        }

        $codes['terminal'] = $locations;

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
    
    public function getTerminalAddress($terminal_id){
      if (file_exists($this->_locationFile) && $terminal_id){
        $locationsXMLArray = $this->XMLparser->load($this->_locationFile)->xmlToArray();
        foreach ($locationsXMLArray['LOCATIONS']['_value']['LOCATION'] as $loc_data) {
          if ($loc_data['ZIP'] == $terminal_id){
            $parcel_terminal_address = $loc_data['NAME'].', '.$loc_data['A2_NAME'].', '.$loc_data['A0_NAME'];
            return $parcel_terminal_address;
          }
        }
      }
      return '';
    }

    /**
     * Get tracking
     *
     * @param string|string[] $trackings
     * @return Result|null
     */
    public function getTracking($trackings)
    {
        //$this->setTrackingReqeust();

        if (!is_array($trackings)) {
            $trackings = [$trackings];
        }
        $this->_getXMLTracking($trackings);

        return $this->_result;
    }

    /**
     * Set tracking request
     *
     * @return void
     */
    protected function setTrackingReqeust()
    {
        $r = new \Magento\Framework\DataObject();

        $account = $this->getConfigData('account');
        $r->setAccount($account);

        $this->_rawTrackingRequest = $r;
    }

    /**
     * Send request for tracking
     *
     * @param string[] $tracking
     * @return void
     */
    protected function _getXMLTracking($tracking)
    {
        $this->_result = $this->_trackFactory->create();
       

        $url=$this->getConfigData('production_webservices_url').'/epteavitus/events/from/'.date("c", strtotime("-1 week +1 day")).'/for-client-code/'.$this->getConfigData('account');
        $process = curl_init();
        $additionalHeaders = '';
        curl_setopt($process, CURLOPT_URL, $url); 
        curl_setopt($process, CURLOPT_HTTPHEADER, array('Content-Type: application/xml', $additionalHeaders));
        curl_setopt($process, CURLOPT_HEADER, FALSE);
        curl_setopt($process, CURLOPT_USERPWD, $this->getConfigData('account') . ":" . $this->getConfigData('password'));
        curl_setopt($process, CURLOPT_TIMEOUT, 30);
        curl_setopt($process, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($process, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($process, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($process, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        $return = curl_exec($process);
        curl_close($process);
        $return = str_ireplace(['SOAP-ENV:', 'SOAP:'], '', $return);
        $xml=simplexml_load_string($return);
        
        $debugData['return'] = $xml;
       
        //$this->_debug($debugData);

        $this->_parseXmlTrackingResponse($tracking, $return);
    }

    /**
     * Parse tracking response
     *
     * @param string $trackingValue
     * @param \stdClass $response
     * @return void
     */
    protected function _parseXmlTrackingResponse($trackings, $response)
    {
        $errorTitle = __('Unable to retrieve tracking');
        $resultArr = [];

        if (strlen(trim($response)) > 0) {
            $response = str_ireplace(['SOAP-ENV:', 'SOAP:'], '', $response);
            $xml = $this->parseXml($response, 'Magento\Shipping\Model\Simplexml\Element');
            if (!is_object($xml)) {
                $errorTitle = __('Response is in the wrong format');
            }
             //$this->_debug($xml);
            if (is_object($xml) && is_object($xml->event)) {
                foreach ($xml->event as $awbinfo) {
                    $awbinfoData = [];
                    $trackNum = isset($awbinfo->packetCode) ? (string)$awbinfo->packetCode : '';
                    if (!in_array($trackNum,$trackings))
                        continue;
                    //$this->_debug($awbinfo);
                    $packageProgress = [];
                    if (isset($resultArr[$trackNum]['progressdetail']))
                        $packageProgress = $resultArr[$trackNum]['progressdetail'];

                    $shipmentEventArray = [];
                    $shipmentEventArray['activity'] = $this->getCode('tracking',(string)$awbinfo->eventCode);
                    $datetime = \DateTime::createFromFormat('U', strtotime($awbinfo->eventDate));
                    $this->_debug(\DateTime::ISO8601);
                    $shipmentEventArray['deliverydate'] = '';//date("Y-m-d", strtotime((string)$awbinfo->eventDate));
                    $shipmentEventArray['deliverytime'] = '';//date("H:i:s", strtotime((string)$awbinfo->eventDate));
                    $shipmentEventArray['deliverylocation'] = $awbinfo->eventSource;
                    $packageProgress[] = $shipmentEventArray;
                        
                    $awbinfoData['progressdetail'] = $packageProgress;
                    
                    $resultArr[$trackNum] = $awbinfoData;
                }
            }
        }

        $result = $this->_trackFactory->create();

        if (!empty($resultArr)) {
            foreach ($resultArr as $trackNum => $data) {
                $tracking = $this->_trackStatusFactory->create();
                $tracking->setCarrier($this->_code);
                $tracking->setCarrierTitle($this->getConfigData('title'));
                $tracking->setTracking($trackNum);
                $tracking->addData($data);
                $result->append($tracking);
            }
        }

        if (!empty($this->_errors) || empty($resultArr)) {
            $resultArr = !empty($this->_errors) ? $this->_errors : $trackings;
            foreach ($resultArr as $trackNum => $err) {
                $error = $this->_trackErrorFactory->create();
                $error->setCarrier($this->_code);
                $error->setCarrierTitle($this->getConfigData('title'));
                $error->setTracking(!empty($this->_errors) ? $trackNum : $err);
                $error->setErrorMessage(!empty($this->_errors) ? $err : $errorTitle);
                $result->append($error);
            }
        }

        $this->_result = $result;
    }

    /**
     * Get tracking response
     *
     * @return string
     */
    public function getResponse()
    {
        $statuses = '';
        if ($this->_result instanceof \Magento\Shipping\Model\Tracking\Result) {
            if ($trackings = $this->_result->getAllTrackings()) {
                foreach ($trackings as $tracking) {
                    if ($data = $tracking->getAllData()) {
                        if (!empty($data['status'])) {
                            $statuses .= __($data['status']) . "\n<br/>";
                        } else {
                            $statuses .= __('Empty response') . "\n<br/>";
                        }
                    }
                }
            }
        }
        if (empty($statuses)) {
            $statuses = __('Empty response');
        }

        return $statuses;
    }

    /**
     * Get allowed shipping methods
     *
     * @return array
     */
    public function getAllowedMethods()
    {
        $allowed = explode(',', $this->getConfigData('allowed_methods'));
        $arr = [];
        foreach ($allowed as $k) {
            $arr[$k] = $this->getCode('method', $k);
        }

        return $arr;
    }

   /**
     * Form XML for shipment request
     *
     * @param \Magento\Framework\DataObject $request
     * @return string
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _formShipmentRequest(\Magento\Framework\DataObject $request)
    {
        $itemsShipment = $request->getPackageItems();
        foreach ($itemsShipment as $itemShipment) {
            $item = new \Magento\Framework\DataObject();
            $item->setData($itemShipment);
            $this->_debug($item);
        }
        $send_method = trim($request->getShippingMethod());
        $pickup_method = $this->getConfigData('pickup');
        $service = "";
        switch ($pickup_method.' '.$send_method){
            case 'COURIER PARCEL_TERMINAL':
                $service = "PU";
                break;
            case 'COURIER COURIER':
                $service = "QH";
                break;
            case 'PARCEL_TERMINAL COURIER':
                $service = "PK";
                break;
            case 'PARCEL_TERMINAL PARCEL_TERMINAL':
                $service = "PA";
                break;
            default:
                $service = "";
                break;
        }
        $parcel_terminal = "";

        if ($send_method == "PARCEL_TERMINAL")
            $parcel_terminal = 'offloadPostcode="'.$request->getOrderShipment()->getOrder()->getShippingAddress()->getOmnivaltParcelTerminal().'" ';
        $payment_method = $request->getOrderShipment()->getOrder()->getPayment()->getMethodInstance()->getCode();
        $cod  = "";
        if ($payment_method == 'msp_cashondelivery') {
          $cod = '<monetary_values>
              <cod_receiver>' . $this->getConfigData('cod_company') . '</cod_receiver>
              <values code="item_value" amount="' . round($request->getOrderShipment()->getOrder()->getGrandTotal(), 2) . '"/>
            </monetary_values>
            <account>' . $this->getConfigData('cod_bank_account') . '</account>
            <reference_number>' . $this->getReferenceNumber($request->getOrderShipment()->getOrder()->getId()) . '</reference_number>';
        }
        $additionalService = '';
        if ($service == "PA" || $service == "PU")
            $additionalService = '
                <add_service>
                     <option code="ST" />
                </add_service>';
        if (($service == "PA" || $service == "PU") && $cod)
          $additionalService = '
                    <add_service>
                         <option code="ST" />
                         <option code="BP" />
                    </add_service>';
        $name             = $this->getConfigData('cod_company');
        $phone            = $this->getConfigData('company_phone');
        $street           = $this->getConfigData('company_address');
        $postcode         = $this->getConfigData('company_postcode');
        $city             = $this->getConfigData('company_city');
        $country          = $this->getConfigData('company_countrycode');
        
        $client_address = 'postcode="'.$request->getRecipientAddressPostalCode().'" deliverypoint="'.$request->getRecipientAddressCity().'" country="'.$request->getRecipientAddressCountryCode().'" street="'.$request->getRecipientAddressStreet1().'" ';
        if ($parcel_terminal)
          $client_address .= $parcel_terminal;
        $xmlRequest = '
        <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsd="http://service.core.epmx.application.eestipost.ee/xsd">
           <soapenv:Header/>
           <soapenv:Body>
              <xsd:businessToClientMsgRequest>
                 <partner>'.$this->getConfigData('account').'</partner>
                 <interchange msg_type="info11">
                    <header file_id="'.\Date('YmdHms').'" sender_cd="'.$this->getConfigData('account').'" >                
                    </header>
                    <item_list>
                       <!--1 or more repetitions:-->
                       <item service="'.$service.'" >
                          '.$additionalService.'
                          <measures weight="'.$request->getPackageWeight().'" />
                          ' . $cod . '
                          <receiverAddressee >
                             <person_name>'.$request->getRecipientContactPersonName().'</person_name>
                             <mobile>'.$request->getRecipientContactPhoneNumber().'</mobile>
                             <address  '. $client_address . ' ></address>
                          </receiverAddressee>
                          <!--Optional:-->
                          <returnAddressee >
                             <person_name>'.$name.'</person_name>
                             <phone>'.$phone.'</phone>
                             <address postcode="'.$postcode .'" deliverypoint="'.$city.'" country="'.$country.'" street="'.$street.'" ></address>
                          </returnAddressee>
                       </item>
                    </item_list>
                 </interchange>
              </xsd:businessToClientMsgRequest>
           </soapenv:Body>
        </soapenv:Envelope>';
        return $xmlRequest;
    }
    
  public function call_omniva(){
    $result = new \Magento\Framework\DataObject();
    $service = "QH";  
    $pickStart = $this->getConfigData('pick_up_time_start')?$this->getConfigData('pick_up_time_start'):'8:00';
    $pickFinish = $this->getConfigData('pick_up_time_finish')?$this->getConfigData('pick_up_time_finish'):'17:00';
    $pickDay = date('Y-m-d');
    if (time() > strtotime($pickDay.' '.$pickFinish))
          $pickDay = date('Y-m-d',strtotime($pickDay . "+1 days"));
        $xmlRequest = '
        <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsd="http://service.core.epmx.application.eestipost.ee/xsd">
           <soapenv:Header/>
           <soapenv:Body>
  <xsd:businessToClientMsgRequest>
     <partner>'.$this->getConfigData('account').'</partner>
     <interchange msg_type="info11">
        <header file_id="'.\Date('YmdHms').'" sender_cd="'.$this->getConfigData('account').'" >    
        </header>
        <item_list>
          ';
          for ($i = 0; $i <1; $i++):
          $xmlRequest .= '
           <item service="'.$service.'" >
  <measures weight="1" />
  <receiverAddressee >
     <person_name>'.$this->getConfigData('cod_company').'</person_name>
     <phone>' . $this->getConfigData('company_phone') . '</phone>
     <address postcode="' . $this->getConfigData('company_postcode') . '" deliverypoint="' . $this->getConfigData('company_city') . '" country="' . $this->getConfigData('company_countrycode') . '" street="' . $this->getConfigData('company_address') . '" />
  </receiverAddressee>
  <!--Optional:-->
  <returnAddressee>
     <person_name>' . $this->getConfigData('cod_company') . '</person_name>
     <!--Optional:-->
     <phone>' . $this->getConfigData('company_phone') . '</phone>
     <address postcode="' . $this->getConfigData('company_postcode') . '" deliverypoint="' . $this->getConfigData('company_city') . '" country="' . $this->getConfigData('company_countrycode') . '" street="' . $this->getConfigData('company_address') . '" />
  </returnAddressee>';
  $xmlRequest .= '
  <onloadAddressee>
     <person_name>' . $this->getConfigData('cod_company') . '</person_name>
     <!--Optional:-->
     <phone>' . $this->getConfigData('company_phone') . '</phone>
     <address postcode="' . $this->getConfigData('company_postcode') . '" deliverypoint="' . $this->getConfigData('company_city') . '" country="' . $this->getConfigData('company_countrycode') . '" street="' . $this->getConfigData('company_address') . '" />
     <pick_up_time start="' . date("c", strtotime($pickDay . ' ' . $pickStart)) . '" finish="' . date("c", strtotime($pickDay . ' ' . $pickFinish)) . '"/>
  </onloadAddressee>';
           $xmlRequest .= '</item>';
          endfor; 
           $xmlRequest .= '
        </item_list>
     </interchange>
  </xsd:businessToClientMsgRequest>
           </soapenv:Body>
        </soapenv:Envelope>';
        
    $url = $this->getConfigData('production_webservices_url').'/epmx/services/messagesService.wsdl';
        $headers = array(
      "Content-type: text/xml;charset=\"utf-8\"",
      "Accept: text/xml",
      "Cache-Control: no-cache",
      "Pragma: no-cache",
      "Content-length: ".strlen($xmlRequest),
              ); 
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_HEADER, 0);
      curl_setopt($ch, CURLOPT_USERPWD, $this->getConfigData('account') . ":" . $this->getConfigData('password'));
      curl_setopt($ch, CURLOPT_POST, 1);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlRequest);
      curl_setopt($ch, CURLOPT_TIMEOUT, 30);
      curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
      $xmlResponse = curl_exec($ch);
    if ($xmlResponse === false) {
      throw new \Exception(curl_error($ch));
    } else {
      $errorTitle = '';
      if (strlen(trim($xmlResponse)) > 0) {
        $xml = $this->parseXml(str_ireplace(['SOAP-ENV:', 'SOAP:'], '', $xmlResponse), 'Magento\Shipping\Model\Simplexml\Element');
        if (!is_object($xml)) {
          $errorTitle = __('Response is in the wrong format');
        }
        if (is_object($xml) && is_object($xml->Body->businessToClientMsgResponse->faultyPacketInfo->barcodeInfo)) {
          foreach ($xml->Body->businessToClientMsgResponse->faultyPacketInfo->barcodeInfo as $data) {
            $errorTitle .= $data->clientItemId.' - '.$data->barcode.' - '.$data->message;
          }
        }
        if ($errorTitle != '')
          $result->setErrors($errorTitle);
        if (!$result->hasErrors()){
          if (is_object($xml) && is_object($xml->Body->businessToClientMsgResponse->savedPacketInfo->barcodeInfo)) {
            foreach ($xml->Body->businessToClientMsgResponse->savedPacketInfo->barcodeInfo as $data) {
              $barcodes[] = (string)$data->barcode;
            }
          }
        }   
      }
    }
    if ($result->hasErrors() || empty($xmlResponse)) {
      return false;
    } else {
      if (!empty($barcodes))
         return $this->_getShipmentLabels($barcodes);
      $result->setErrors(__('No saved barcodes received'));
      return false;
    }
  }
    
  protected function getReferenceNumber($order_number)
  {
    $order_number = (string) $order_number;
    $kaal         = array(7,3,1);
    $sl           = $st = strlen($order_number);
    $total        = 0;
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
    protected function _getShipmentLabels($barcodes)
    {
        $barcodeXML = '';
        foreach ($barcodes as $barcode){
            $barcodeXML .= '<barcode>'.$barcode.'</barcode>';
        }
        $xmlRequest = '
        <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsd="http://service.core.epmx.application.eestipost.ee/xsd">
           <soapenv:Header/>
           <soapenv:Body>
              <xsd:addrcardMsgRequest>
                 <partner>'.$this->getConfigData('account').'</partner>
                 <sendAddressCardTo>response</sendAddressCardTo>
                 <barcodes>
                    '.$barcodeXML.'
                 </barcodes>
              </xsd:addrcardMsgRequest>
           </soapenv:Body>
        </soapenv:Envelope>';
        $debugData = ['request' => $xmlRequest];        

        try {
            $url = $this->getConfigData('production_webservices_url').'/epmx/services/messagesService.wsdl';
            $headers = array(
                        "Content-type: text/xml;charset=\"utf-8\"",
                        "Accept: text/xml",
                        "Cache-Control: no-cache",
                        "Pragma: no-cache",
                        "Content-length: ".strlen($xmlRequest),
                    ); 
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_USERPWD, $this->getConfigData('account') . ":" . $this->getConfigData('password'));
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlRequest);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $xmlResponse = curl_exec($ch);
            $debugData['result'] = $xmlResponse;
        } catch (\Exception $e) {
            $debugData['result'] = ['error' => $e->getMessage(), 'code' => $e->getCode()];
            $xmlResponse = '';
        }
        $xmlResponse = str_ireplace(['SOAP-ENV:', 'SOAP:'], '', $xmlResponse);
        try {
            $response = $this->_xmlElFactory->create(['data' => $xmlResponse]);
        } catch (\Exception $e) {
            $debugData['result'] = ['error' => $e->getMessage(), 'code' => $e->getCode()];
        }

        $result = new \Magento\Framework\DataObject();
        if (isset($response->Error)) {
            $result->setErrors((string)$response->Error->ErrorDescription);
        } else {
            $xml = $this->parseXml($xmlResponse, 'Magento\Shipping\Model\Simplexml\Element');
             if (!is_object($xml)) {
                $result->setErrors(__('Response is in the wrong format'));
            }
            if (is_object($xml) && is_object($xml->Body->addrcardMsgResponse->successAddressCards->addressCardData->barcode)) {
                $shippingLabelContent = (string)$xml->Body->addrcardMsgResponse->successAddressCards->addressCardData->fileData;
                $trackingNumber = (string)$xml->Body->addrcardMsgResponse->successAddressCards->addressCardData->barcode;

                $result->setShippingLabelContent(base64_decode($shippingLabelContent));
                $result->setTrackingNumber($trackingNumber);
            } else {
                $result->setErrors(__('No label received from webservice'));
            }
        }

        //$this->_debug($debugData);

        return $result;
    }

    /**
     * Do shipment request to carrier web service, obtain Print Shipping Labels and process errors in response
     *
     * @param \Magento\Framework\DataObject $request
     * @return \Magento\Framework\DataObject
     * @throws \Exception
     */
    protected function _doShipmentRequest(\Magento\Framework\DataObject $request)
    {
        $barcodes = array();
        $this->_prepareShipmentRequest($request);
        $result = new \Magento\Framework\DataObject();
        $xmlRequest = $this->_formShipmentRequest($request);
        
        $url = $this->getConfigData('production_webservices_url').'/epmx/services/messagesService.wsdl';
        $headers = array(
                        "Content-type: text/xml;charset=\"utf-8\"",
                        "Accept: text/xml",
                        "Cache-Control: no-cache",
                        "Pragma: no-cache",
                        "Content-length: ".strlen($xmlRequest),
                    ); 
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_USERPWD, $this->getConfigData('account') . ":" . $this->getConfigData('password'));
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlRequest);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $xmlResponse = curl_exec($ch);
            if ($xmlResponse === false) {
                throw new \Exception(curl_error($ch));
            } else {
                $debugData['result'] = $xmlResponse;
                $errorTitle = '';
                if (strlen(trim($xmlResponse)) > 0) {
                    $xml = $this->parseXml(str_ireplace(['SOAP-ENV:', 'SOAP:'], '', $xmlResponse), 'Magento\Shipping\Model\Simplexml\Element');
                    if (!is_object($xml)) {
                        $errorTitle = __('Response is in the wrong format');
                    }
                    //$this->_debug($xml->Body->businessToClientMsgResponse->faultyPacketInfo);
                    if (is_object($xml) && is_object($xml->Body->businessToClientMsgResponse->faultyPacketInfo->barcodeInfo)) {
                        foreach ($xml->Body->businessToClientMsgResponse->faultyPacketInfo->barcodeInfo as $data) {
                            $errorTitle .= $data->clientItemId.' - '.$data->barcode.' - '.$data->message;
                        }
                    }
                    if ($errorTitle != '')
                        $result->setErrors($errorTitle);
                    if (!$result->hasErrors()){
                        if (is_object($xml) && is_object($xml->Body->businessToClientMsgResponse->savedPacketInfo->barcodeInfo)) {
                            foreach ($xml->Body->businessToClientMsgResponse->savedPacketInfo->barcodeInfo as $data) {
                                $barcodes[] = (string)$data->barcode;
                            }
                        }
                    }
                    
                }
            }
       // }
        $debugData['barcodes'] = $barcodes;

        if ($result->hasErrors() || empty($xmlResponse)) {
            return $result;
        } else {
            if (!empty($barcodes))
               return $this->_getShipmentLabels($barcodes);
            $result->setErrors(__('No saved barcodes received'));
            return $result;
        }
        
    }



    /**
     * @param array|object $trackingIds
     * @return string
     */
    private function getTrackingNumber($trackingIds) {
        return is_array($trackingIds) ? array_map(
            function($val) {
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
    public function rollBack($data)
    {
        
    }

   

    /**
     * Return delivery confirmation types of carrier
     *
     * @param \Magento\Framework\DataObject|null $params
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getDeliveryConfirmationTypes(\Magento\Framework\DataObject $params = null)
    {
        return $this->getCode('delivery_confirmation_types');
    }

    /**
     * Recursive replace sensitive fields in debug data by the mask
     * @param string $data
     * @return string
     */
    protected function filterDebugData($data)
    {
        foreach (array_keys($data) as $key) {
            if (is_array($data[$key])) {
                $data[$key] = $this->filterDebugData($data[$key]);
            } elseif (in_array($key, $this->_debugReplacePrivateDataKeys)) {
                $data[$key] = self::DEBUG_KEYS_MASK;
            }
        }
        return $data;
    }
    

    /**
     * Append error message to rate result instance
     * @param string $trackingValue
     * @param string $errorMessage
     * @return void
     */
    private function appendTrackingError($trackingValue, $errorMessage)
    {
        $error = $this->_trackErrorFactory->create();
        $error->setCarrier(self::CODE);
        $error->setCarrierTitle($this->getConfigData('title'));
        $error->setTracking($trackingValue);
        $error->setErrorMessage($errorMessage);
        $result = $this->getResult();
        $result->append($error);
    }
}
