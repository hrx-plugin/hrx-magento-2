<?php

namespace Hrx\Shipping\Controller\Adminhtml\Order;

use Hrx\Shipping\Model\HrxOrderFactory;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use \setasign\Fpdi\Fpdi;
use Magento\Sales\Model\Order;

class Action extends \Magento\Backend\App\Action implements HttpPostActionInterface
{

  protected $resultPageFactory;
  protected $hrxOrderFactory;
  protected $orderRepository;
  protected $hrxCarrier;
  protected $resultJsonFactory;

  public function __construct(
              \Magento\Backend\App\Action\Context $context,
              \Magento\Framework\View\Result\PageFactory $resultPageFactory,
              \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
              HrxOrderFactory $hrxOrderFactory,
              \Hrx\Shipping\Model\Carrier $hrxCarrier,
              \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
  ){
       parent::__construct($context);
      $this->resultPageFactory = $resultPageFactory;
      $this->hrxOrderFactory = $hrxOrderFactory;
      $this->orderRepository = $orderRepository;
      $this->hrxCarrier = $hrxCarrier;
      $this->resultJsonFactory = $resultJsonFactory;
  }

  public function execute()
  {
    $isPost = $this->getRequest()->getPost();
    $resultJson = $this->resultJsonFactory->create();
    $errors = [];
    $stickers = [];
        if ($isPost) {
            $ids = $this->getRequest()->getParam('ids');
            $action = $this->getRequest()->getParam('action');
            if ($ids) {
              $order_ids = explode(';', trim($ids,';'));
              foreach ($order_ids as $order_id) {
                $model = $this->hrxOrderFactory->create();
                $model->load($order_id, 'order_id');
                $order = $this->hrxCarrier->getOrder($model->getOrderId());
                try {
                  if ($model->getId() && $action){
                    if ($action == 'ready' && ($model->getStatus() == 'new' || $model->getStatus() == 'error')) {
                      $this->hrxCarrier->createHrxShipment($model, $order);
                      $order->setIsInProcess(true);
                      $order->addStatusHistoryComment('Automatically SHIPPED by HRX delivery.', false);
                      $order->setState(Order::STATE_COMPLETE)->setStatus(Order::STATE_COMPLETE);
                      $order->save();
                    }
                    if ($action == 'label' && !in_array($model->getStatus(),['new','deleted','canceled'])) {
                      $sticker = $this->hrxCarrier->getLabel($model);
                      if (isset($sticker['file_content'])) {
                          $stickers[] = $sticker['file_content'];
                      }
                    }
                    if ($action == 'return_label' && !in_array($model->getStatus(),['new','deleted','canceled'])) {
                      $sticker = $this->hrxCarrier->getReturnLabel($model);
                      if (isset($sticker['file_content'])) {
                          $stickers[] = $sticker['file_content'];
                      }
                    }
                    if ($action == 'delete') {
                      $this->hrxCarrier->cancelOrder($model);
                      $model->setStatus('cancelled');
                      $model->save();
                    }
                  }
                } catch (\Throwable $e) {
                  $errors[] = $order->getIncrementId() . ': ' . $e->getMessage();
                } 
              }
            }
        if (!empty($errors)) {
          return $resultJson->setData(['status' => 'error', 'messages' => $errors]);
        }    
      }
      $data = ['status' => 'ok'];
      if (!empty($stickers)) {
          $data['pdf'] = $this->mergePdf($stickers);
      }
      return $resultJson->setData($data);
        
  }

  private function mergePdf($pdfs) {
    $pageCount = 0;
    // initiate FPDI
    $pdf = new Fpdi();

    foreach ($pdfs as $data) {
        $name = tempnam("/tmp", "tmppdf");
        $handle = fopen($name, "w");
        fwrite($handle, base64_decode($data));
        fclose($handle);

        $pageCount = $pdf->setSourceFile($name);
        for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
            $templateId = $pdf->importPage($pageNo);
            
            $pdf->AddPage('P');
            
            $pdf->useTemplate($templateId, ['adjustPageSize' => true]);
        }
    }
    return base64_encode($pdf->Output('S'));
  }
}