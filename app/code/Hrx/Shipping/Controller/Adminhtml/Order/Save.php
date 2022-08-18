<?php

namespace Hrx\Shipping\Controller\Adminhtml\Order;

use Hrx\Shipping\Model\HrxOrderFactory;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;

class Save extends \Magento\Backend\App\Action implements HttpPostActionInterface
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
              \Hrx\Shipping\Model\Carrier $hrxCarrier,
              HrxOrderFactory $hrxOrderFactory,
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
    $resultJson = $this->resultJsonFactory->create();
    $isPost = $this->getRequest()->getPost();

        if ($isPost) {
            $model = $this->hrxOrderFactory->create();
            $order_id = $this->getRequest()->getParam('order_id');
            if ($order_id) {
              $model->load($order_id, 'order_id');
              if ($model->getId()){
                $order = $this->orderRepository->get($order_id);
                $old_data = $model->getData();
                $data = [
                  'hrx_terminal_id' => $this->getRequest()->getParam('terminal_id'),
                  'hrx_warehouse_id' => $this->getRequest()->getParam('warehouse_id'),
                  'length' => $this->getRequest()->getParam('length'),
                  'width' => $this->getRequest()->getParam('width'),
                  'height' => $this->getRequest()->getParam('height'),
                  'weight' => $this->getRequest()->getParam('weight'),
                ];
                $model->setData(array_merge($old_data , $data));

                if ($order->getShippingMethod() == 'hrx_courier') {
                  $terminal = $this->hrxCarrier->getLocation($order->getShippingAddress()->getCountryId());
                } else {
                  $terminal = $this->hrxCarrier->getTerminal($model->getHrxTerminalId());
                }
                if ($terminal->getId()) {
                  $measurement_errors = [];
                  if ($terminal->getMinWeight() != null) {
                    if ($model->getWeight() < $terminal->getMinWeight() || $model->getWeight() > $terminal->getMaxWeight()) {
                        $errors[] = "Weight must be between: " . $terminal->getMinWeight() . ' - ' . $terminal->getMaxWeight();
                    }
                    if ($model->getWidth() < $terminal->getMinWidth() || $model->getWidth() > $terminal->getMaxWidth()) {
                        $errors[] = "Width must be between: " . $terminal->getMinWidth() . ' - ' . $terminal->getMaxWidth();
                    }
                    if ($model->getHeight() < $terminal->getMinHeight() || $model->getHeight() > $terminal->getMaxHeight()) {
                        $errors[] = "Height must be between: " . $terminal->getMinHeight(). ' - ' . $terminal->getMaxHeight();
                    }
                    
                    if ($model->getLength() < $terminal->getMinLength() || $model->getLength() > $terminal->getMaxLength()) {
                        $errors[] = "Length must be between: " . $terminal->getMinLength() . ' - ' . $terminal->getMaxLength();
                    }
                  }
                  if (!empty($errors)) {
                      return $resultJson->setData([
                          'status' => 'error',
                          'messages' => $errors
                      ]);
                  }
                }
                $model->save();

                if ($order && $data['hrx_terminal_id']) {
                  $shippingAddress = $order->getShippingAddress();
                  $shippingAddress->setHrxParcelTerminal($data['hrx_terminal_id']);
                  $shippingAddress->save();
                }
              }
            }
          }
          return $resultJson->setData([
            'status' => 'ok'
        ]);
  }
}