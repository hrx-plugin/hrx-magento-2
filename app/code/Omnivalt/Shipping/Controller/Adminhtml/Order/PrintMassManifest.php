<?php

namespace Omnivalt\Shipping\Controller\Adminhtml\Order;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class MassManifest
 */
class PrintMassManifest extends \Magento\Sales\Controller\Adminhtml\Order\AbstractMassAction
{
  /**
   * @var OrderManagementInterface
   */
  protected $orderManagement;
  protected $omnivalt_carrier;
  public $labelsContent = array();
  
  /**
   * @param Context $context
   * @param Filter $filter
   * @param CollectionFactory $collectionFactory
   * @param OrderManagementInterface $orderManagement
   * @param ScopeConfigInterface $scopeConfig
   */
  public function __construct(Context $context, Filter $filter, CollectionFactory $collectionFactory, OrderManagementInterface $orderManagement, \Omnivalt\Shipping\Model\Carrier $omnivalt_carrier)
  {
    
    $this->collectionFactory = $collectionFactory;
    $this->orderManagement   = $orderManagement;
    $this->omnivalt_carrier = $omnivalt_carrier;
    parent::__construct($context, $filter);
  }
  
  public function isOmnivaltMethod($order)
  {
    $_omnivaltMethods      = array(
      'omnivalt_PARCEL_TERMINAL',
      'omnivalt_COURIER'
    );
    $order_shipping_method = $order->getData('shipping_method');
    return in_array($order_shipping_method, $_omnivaltMethods);
  }
  
  private function _collectPostData($post_key = null)
  {
    return $this->getRequest()->getPost($post_key);
  }
  
  private function _fillDataBase(AbstractCollection $collection)
  {
    $pack_data = array();
    $model     = $this->_objectManager->create('Magento\Sales\Model\Order');
    $unique = array();
    foreach ($collection->getItems() as $order) {
      if (!$order->getEntityId()) {
        continue;
      }
      if (in_array($order->getEntityId(),$unique))
        continue;
      $unique[] = $order->getEntityId();
      $pack_no = array();
      
      if (!$this->isOmnivaltMethod($order)) {
        $text = 'Warning: Order ' . $order->getData('increment_id') . ' not Omnivalt shipping method.';
        $this->messageManager->addError($text);
        continue;
      }
      if (!$order->getShippingAddress()) { //Is set Shipping adress?
        $items = $order->getAllVisibleItems();
        foreach ($items as $item) {
          $ordered_items['sku'][]  = $item->getSku();
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
  
  
  public function massAction(AbstractCollection $collection)
  {
    $pack_data     = array();
    $success_files = array();
    $order_ids     = $this->_collectPostData('order_ids');
    $model         = $this->_objectManager->create('Magento\Sales\Model\Order');
    $pack_data     = $this->_fillDataBase($collection); //Send data to server and get packs number's
    
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
    $order_table   = '';
    $count         = 0;
    $last_order    = false;
    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    $order_ids     = array();
    foreach ($pack_data as $order) {
      $track_numer = '';
      foreach ($order->getShipmentsCollection() as $shipment) {
        foreach ($shipment->getAllTracks() as $tracknum) {
          $track_numer .= $tracknum->getNumber() . ' ';
        }
      }
      if ($track_numer == '') {
        $text = 'Warning: Order ' . $order->getData('increment_id') . ' has no tracking number. Will not be included in manifest.';
        $this->messageManager->addWarning($text);
        continue;
      }
      $order->setManifestGenerationDate($generation_date);
      $order->save();
      $count++;
      $storeManager    = $objectManager->get('\Magento\Framework\App\Config\ScopeConfigInterface');
      $shippingAddress = $order->getShippingAddress();
      $country         = $objectManager->create('\Magento\Directory\Model\Country')->load($shippingAddress->getCountryId());
      $street          = $shippingAddress->getStreet();
      $parcel_terminal_address = '';
      if ($order->getData('shipping_method') == 'omnivalt_PARCEL_TERMINAL'){
        $shippingAddress = $order->getShippingAddress();
        $terminal_id = $shippingAddress->getOmnivaltParcelTerminal();
        $parcel_terminal_address = $this->omnivalt_carrier->getTerminalAddress($terminal_id);
      }
      $client_address = $shippingAddress->getName() . ', ' . $street[0] . ', ' . $shippingAddress->getPostcode() . ', ' . $shippingAddress->getCity() . ' ' . $country->getName();
      if ($parcel_terminal_address != '')
          $client_address = '';
      $order_table .= '<tr><td width = "40" align="right">' . $count . '.</td><td>' . $track_numer . '</td><td width = "60">' . date('Y-m-d') . '</td><td width = "40">1</td><td width = "60">' . $order->getWeight() . '</td><td width = "210">' . $client_address . $parcel_terminal_address . '</td></tr>';
      
      $last_order = $order;
    }
    /*
    $storeInformation = $objectManager->create('\Magento\Framework\App\Config\ScopeConfigInterface');
    $name             = $storeInformation->getValue('general/store_information/name', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    $street           = $storeInformation->getValue('general/store_information/street_line1', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    $postcode         = $storeInformation->getValue('general/store_information/postcode', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    $city             = $storeInformation->getValue('general/store_information/city', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    $country          = $storeInformation->getValue('general/store_information/country_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    */
    $name             = $this->omnivalt_carrier->getConfigData('cod_company');
    $phone            = $this->omnivalt_carrier->getConfigData('company_phone');
    $street           = $this->omnivalt_carrier->getConfigData('company_address');
    $postcode         = $this->omnivalt_carrier->getConfigData('company_postcode');
    $city             = $this->omnivalt_carrier->getConfigData('company_city');
    $country          = $this->omnivalt_carrier->getConfigData('company_countrycode');

    $pdf->SetFont('freeserif', '', 14);
    $country   = $objectManager->create('\Magento\Directory\Model\Country')->load($country);
    $shop_addr = '<table cellspacing="0" cellpadding="1" border="0"><tr><td>' . date('Y-m-d H:i:s') . '</td><td>'. __('Sender Address') .':<br/>' . $name . '<br/>' . $street . ', ' . $postcode . '<br/>' . $city . ', ' . $country->getName() . '<br/></td></tr></table>';
    $pdf->writeHTML($shop_addr, true, false, false, false, '');
    $tbl = '
        <table cellspacing="0" cellpadding="4" border="1">
          <thead>
            <tr>
              <th width = "40" align="right">Nr.</th>
              <th>'. __('Parcel Nr.') .'</th>
              <th width = "60">'. __('Date') .'</th>
              <th width = "40" >'. __('Amount') .'</th>
              <th width = "60">'. __('Weight') .' (kg)</th>
              <th width = "210">'. __('Receiver') .'</th>
            </tr>
          </thead>
          <tbody>
          ' . $order_table . '
          </tbody>
        </table><br/><br/>
    ';
    if ($count > 0){
      //$result = $this->omnivalt_carrier->call_omniva();
      //var_dump( $result); exit;
    } else {
      $this->_redirect($this->_redirect->getRefererUrl());
      return;
    }
    $pdf->SetFont('freeserif', '', 9);
    $pdf->writeHTML($tbl, true, false, false, false, '');
    $pdf->SetFont('freeserif', '', 14);
    $sign = __('Courier full name, signature').' ________________________________________________<br/><br/>';
    $sign .= __('Sender full name, signature').' ________________________________________________';
    $pdf->writeHTML($sign, true, false, false, false, '');
    $pdf->Output('Omnivalt_manifest_' . date('Y-m-d H.i.s') . '.pdf', 'D');
  }
  
}
