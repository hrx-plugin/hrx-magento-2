<?php
namespace Hrx\Shipping\Controller\Adminhtml\Hrxmanifest;


class Index extends  \Magento\Backend\App\Action
{

  protected $resultPageFactory;

  public function __construct(
              \Magento\Backend\App\Action\Context $context,
              \Magento\Framework\View\Result\PageFactory $resultPageFactory
  ){
       parent::__construct($context);
      $this->resultPageFactory = $resultPageFactory;
  }

  public function execute()
  {

      $resultPage = $this->resultPageFactory->create();
      $resultPage->getConfig()->getTitle()->prepend(__('Hrx manifest'));

      return $resultPage;
  }
}