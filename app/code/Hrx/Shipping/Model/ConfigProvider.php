<?php

namespace Hrx\Shipping\Model;

use Magento\Checkout\Model\ConfigProviderInterface;

/**
 * Class SampleConfigProvider
 */
class ConfigProvider implements ConfigProviderInterface
{
    protected $scopeConfig;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig()
    {

        $config = [
            'hrxGlobalData' => [
                'distance' => $this->scopeConfig->getValue('carriers/hrxglobal/hrx_methods_group/terminal_distance') ?? 2,
                'apiUrl' => $this->scopeConfig->getValue('carriers/hrxglobal/production_webservices_url'),
            ]
        ];

        return $config;
    }
}
