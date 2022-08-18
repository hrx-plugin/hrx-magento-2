<?php

namespace Hrx\Shipping\Cron;

use Hrx\Shipping\Model\Carrier;

class WarehouseUpdate
{
    protected $hrxCarrier;
    protected $_logger;

	public function __construct(
		Carrier $hrxCarrier,
        \Psr\Log\LoggerInterface $logger
	) {
		$this->hrxCarrier = $hrxCarrier;
        $this->_logger = $logger;
	}

	public function execute() {
		try {
			$this->hrxCarrier->updateWarehouses();
            $this->_logger->info('HRX Warehouses updated');
		} catch (\Throwable $e) {
			$this->_logger->info($e->getMessage());
		}	
		return $this;

	}
}