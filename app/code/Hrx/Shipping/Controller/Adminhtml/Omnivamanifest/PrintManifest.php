<?php
namespace Hrx\Shipping\Controller\Adminhtml\Hrxmanifest;

use Magento\Framework\App\CsrfAwareActionInterface; 
use Magento\Framework\App\RequestInterface;  
use Magento\Framework\App\Request\InvalidRequestException;

class PrintManifest extends  \Magento\Framework\App\Action\Action implements CsrfAwareActionInterface
{

  protected $resultPageFactory;
  protected $massManifest;
  protected $_orderCollectionFactory;

  public function __construct(
              \Magento\Backend\App\Action\Context $context,
              \Magento\Framework\View\Result\PageFactory $resultPageFactory,
              \Hrx\Shipping\Controller\Adminhtml\Order\PrintMassManifest $massManifest,
              \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
              
  ){
    
      $this->resultPageFactory = $resultPageFactory;
      $this->massManifest = $massManifest;
      $this->_orderCollectionFactory = $orderCollectionFactory;
       parent::__construct($context);
  }

  public function createCsrfValidationException(RequestInterface $request):  ?InvalidRequestException { return null; } 
  public function validateForCsrf(RequestInterface $request):  ?bool { return true; }

  public function execute()
  {
      $order_ids = $this->getRequest()->getPost('order_ids');
      $collection = $this->_orderCollectionFactory->create()->addFieldToFilter('entity_id',array('in' => $order_ids))->load();
      
      return $this->massManifest->massAction($collection);
  }
}