<?php

namespace Hrx\Shipping\Cron;

use Hrx\Shipping\Model\Carrier;
use Hrx\Shipping\Model\HrxOrderFactory;

class OrderCheck
{
    protected $hrxCarrier;
    protected $hrxOrderFactory;
    protected $_logger;

	public function __construct(
		Carrier $hrxCarrier,
		HrxOrderFactory $hrxOrderFactory,
        \Psr\Log\LoggerInterface $logger
	) {
		$this->hrxCarrier = $hrxCarrier;
		$this->hrxOrderFactory = $hrxOrderFactory;
        $this->_logger = $logger;
	}

	public function execute() {

        //update order statuses
        $allowed_statuses = [
            'ready',
            'in_delivery',
            'delivered',
            'in_return',
            'returned',
            'error',
            'cancelled'
        ];
		try {
			$orders = $this->hrxOrderFactory->create()->getCollection() ->addFieldToSelect('*');
			$orders->addFieldToFilter('hrx_order_id', array(['notnull' => true]))->addFieldToFilter('status', array(['nin' => ['new', 'delivered', 'cancelled','returned']]));
			$this->_logger->info('Found HRX orders: ' . count($orders));
		} catch (\Throwable $e) {
			$this->_logger->info($e->getMessage());
		}	
		if (!empty($orders)) {
			foreach ($orders as $order) {
				try {
					$order_data = $this->hrxCarrier->getHrxApiOrder($order);
					$tracking = $order_data['tracking_number'] ?? null;
                    if ($tracking) {
                        $order->setTracking($tracking);
                    }
                    
                    $tracking_url = $order_data['tracking_url'] ?? null;
                    if ($tracking_url) {
                        $order->setTrackingUrl($tracking_url);
                    }

                    $status = $order_data['status'] ?? null;
                    if (in_array($status, $allowed_statuses)) {
                        //$fulfill = (($status == 'in_delivery' || $status == 'delivered') && $order->getStatus() != $status);
                        $this->_logger->info('Setting HRX order ' . $order->getId() . ' status to ' . $status);
                        $order->setStatus($status);
                    }
                    //$this->_logger->info($order->getStatus());
                    $order->save();
				} catch (\Throwable $e) {
					//echo $e->getMessage();
					$this->_logger->info($e->getMessage());
				}	
			}
		}

		return $this;

	}
}