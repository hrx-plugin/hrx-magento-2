<?php

namespace Hrx\Shipping\Controller\Adminhtml\Order;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Mijora\Hrx\Shipment\Manifest;
use Mijora\Hrx\Shipment\Order;
use Mijora\Hrx\Shipment\Package\Address;
use Mijora\Hrx\Shipment\Package\Contact;

/**
 * Class MassManifest
 */
class PrintMassManifest extends \Magento\Sales\Controller\Adminhtml\Order\AbstractMassAction
{

    /**
     * @var OrderManagementInterface
     */
    protected $orderManagement;
    protected $hrx_carrier;
    public $labelsContent = array();

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param OrderManagementInterface $orderManagement
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(Context $context, Filter $filter, CollectionFactory $collectionFactory, OrderManagementInterface $orderManagement, \Hrx\Shipping\Model\Carrier $hrx_carrier) {

        $this->collectionFactory = $collectionFactory;
        $this->orderManagement = $orderManagement;
        $this->hrx_carrier = $hrx_carrier;
        parent::__construct($context, $filter);
    }

    public function isHrxMethod($order) {
        $_hrxMethods = array(
            'hrx_PARCEL_TERMINAL',
            'hrx_COURIER',
            'hrx_COURIER_PLUS'
        );
        $order_shipping_method = $order->getData('shipping_method');
        return in_array($order_shipping_method, $_hrxMethods);
    }

    private function _collectPostData($post_key = null) {
        return $this->getRequest()->getPost($post_key);
    }

    private function _fillDataBase(AbstractCollection $collection) {
        $pack_data = array();
        $unique = array();
        foreach ($collection->getItems() as $order) {
            if (!$order->getEntityId()) {
                continue;
            }
            if (in_array($order->getEntityId(), $unique))
                continue;
            $unique[] = $order->getEntityId();
            $pack_no = array();

            if (!$this->isHrxMethod($order)) {
                $text = 'Warning: Order ' . $order->getData('increment_id') . ' not Hrx shipping method.';
                $this->messageManager->addError($text);
                continue;
            }
            if (!$order->getShippingAddress()) { //Is set Shipping adress?
                $items = $order->getAllVisibleItems();
                foreach ($items as $item) {
                    $ordered_items['sku'][] = $item->getSku();
                    $ordered_items['type'][] = $item->getProductType();
                }
                $text = 'Warning: Order ' . $order->getData('increment_id') . ' not have Shipping Address.';
                $this->messageManager->addError($text);
                continue;
            }
            $pack_data[] = $order;
        }

        return $pack_data;
    }

    public function massAction(AbstractCollection $collection) {
        $pack_data = $this->_fillDataBase($collection); //Send data to server and get packs number's

        if (!count($pack_data) || $pack_data === false) {
            $text = 'Warning: No orders selected.';
            $this->messageManager->addWarning($text);
            $this->_redirect($this->_redirect->getRefererUrl());
            return;
        }
        $generation_date = date('Y-m-d H:i:s');
        $pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->AddPage();
        $count = 0;
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $name = $this->hrx_carrier->getConfigData('cod_company');
        $phone = $this->hrx_carrier->getConfigData('company_phone');
        $street = $this->hrx_carrier->getConfigData('company_address');
        $postcode = $this->hrx_carrier->getConfigData('company_postcode');
        $city = $this->hrx_carrier->getConfigData('company_city');
        $country = $this->hrx_carrier->getConfigData('company_countrycode');

        try {

            $address = new Address();
            $address
                    ->setCountry($country)
                    ->setPostcode($postcode)
                    ->setDeliverypoint($city)
                    ->setStreet($street);

            // Sender contact data
            $senderContact = new Contact();
            $senderContact
                    ->setAddress($address)
                    ->setMobile($phone)
                    ->setPersonName($name);

            $manifest = new Manifest();
            $manifest->setSender($senderContact);

            foreach ($pack_data as $order) {
                $barcode = '';
                foreach ($order->getShipmentsCollection() as $shipment) {
                    foreach ($shipment->getAllTracks() as $tracknum) {
                        $barcode .= $tracknum->getNumber() . ' ';
                    }
                }
                if ($barcode == '') {
                    $text = 'Warning: Order ' . $order->getData('increment_id') . ' has no tracking number. Will not be included in manifest.';
                    $this->messageManager->addWarning($text);
                    continue;
                }
                $order->setManifestGenerationDate($generation_date);
                $order->save();
                $count++;

                $shippingAddress = $order->getShippingAddress();
                $country = $objectManager->create('\Magento\Directory\Model\Country')->load($shippingAddress->getCountryId());
                $street = $shippingAddress->getStreet();
                $parcel_terminal_address = '';

                if (strtoupper($order->getData('shipping_method')) == strtoupper('hrx_PARCEL_TERMINAL')) {
                    $shippingAddress = $order->getShippingAddress();
                    $terminal_id = $shippingAddress->getHrxParcelTerminal();
                    $order_address = $this->hrx_carrier->getTerminalAddress($terminal_id);
                } else {
                    $order_address = $shippingAddress->getName() . ', ' . $street[0] . ', ' . $shippingAddress->getPostcode() . ', ' . $shippingAddress->getCity() . ' ' . $country->getName();
                }

                $_order = new Order();
                $_order->setTracking($barcode);
                $_order->setQuantity('1');
                $_order->setWeight($order->getWeight());
                $_order->setReceiver($order_address);
                $manifest->addOrder($_order);
            }
            if ($count > 0) {
                $manifest->downloadManifest('D', 'Hrx_manifest_' . date('Y-m-d H.i.s'));
            } else {
                $this->_redirect($this->_redirect->getRefererUrl());
                return;
            }
        } catch (\Exception $e) {
            $this->messageManager->addWarning($e->getMessage());
        }
        $this->_redirect($this->_redirect->getRefererUrl());
        return;
    }

}
