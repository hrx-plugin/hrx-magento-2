<?php

namespace Hrx\Shipping\Cron;

use Hrx\Shipping\Model\Carrier;

class TerminalsUpdate
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
			$this->hrxCarrier->updateParcelTerminals();
			$this->hrxCarrier->updateLocations();
            $this->_logger->info('HRX terminals updated');
		} catch (\Throwable $e) {
			$this->_logger->info($e->getMessage());
		}	
		return $this;

	}
}