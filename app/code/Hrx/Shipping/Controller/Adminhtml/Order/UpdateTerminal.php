<?php

namespace Hrx\Shipping\Controller\Adminhtml\Order;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Hrx\Shipping\Model\Carrier;

/**
 * Class MassManifest
 */
class UpdateTerminal extends \Magento\Framework\App\Action\Action
{
  protected $hrxCarrier;
  protected $orderRepository;
   
  public function __construct(
    Context $context, 
    Filter $filter, 
    CollectionFactory $collectionFactory, 
    OrderManagementInterface $orderManagement, 
    Carrier $hrxCarrier,
    \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
    )
  {
    //$this->_objectManager = $objectmanager;
    $this->orderRepository = $orderRepository;
    $this->hrxCarrier = $hrxCarrier;
    parent::__construct($context);
  }
  
  public function execute()
  {
    $order_id = $this->getRequest()->getParam('order_id');
    $terminal = $this->getRequest()->getParam('terminal_id');
    
    $order = $this->orderRepository->get($order_id);
    if ($order){
        $order_address = $order->getShippingAddress();
        $order_address->setHrxParcelTerminal( $terminal);
        $order_address->save();

        $hrx_order = $this->hrxCarrier->getHrxOrder($order);
        $hrx_order->setHrxTerminalId($terminal);
        $hrx_order->save();

        $text = __('Parcel terminal updated');
        $this->messageManager->addSuccess($text);
    } else {
        $text = __('Parcel terminal not updated');
        $this->messageManager->addError($text);
    }
    $this->_redirect($this->_redirect->getRefererUrl());
    return;
   
  }
  
}