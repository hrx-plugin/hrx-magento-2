<?php
namespace Hrx\Shipping\Controller\Adminhtml\Orders;

use Magento\Framework\App\CsrfAwareActionInterface; 
use Magento\Framework\App\RequestInterface;  
use Magento\Framework\App\Request\InvalidRequestException;

class GenerateLabels extends  \Magento\Framework\App\Action\Action implements CsrfAwareActionInterface
{

  protected $resultPageFactory;
  protected $massLabels;
  protected $_orderCollectionFactory;

  public function __construct(
              \Magento\Backend\App\Action\Context $context,
              \Magento\Framework\View\Result\PageFactory $resultPageFactory,
              \Hrx\Shipping\Controller\Adminhtml\Order\PrintMassLabels $massLabels,
              \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
              
  ){
    
      $this->resultPageFactory = $resultPageFactory;
      $this->massLabels = $massLabels;
      $this->_orderCollectionFactory = $orderCollectionFactory;
       parent::__construct($context);
  }

  public function createCsrfValidationException(RequestInterface $request):  ?InvalidRequestException 
  { 
      return null; 
  } 
  public function validateForCsrf(RequestInterface $request):  ?bool 
  { 
      return true; 
  }

  public function execute()
  {
      $order_ids = $this->getRequest()->getPost('order_ids');
      $collection = $this->_orderCollectionFactory->create()->addFieldToFilter('entity_id',array('in' => $order_ids))->load();
      
      return $this->massLabels->massAction($collection);
  }
}