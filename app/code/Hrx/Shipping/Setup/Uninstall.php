<?php

namespace Hrx\Shipping\Setup;
 
use Magento\Framework\Setup\UninstallInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
 
class Uninstall implements UninstallInterface
{
    public function uninstall(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
 
        $setup->getConnection()->dropColumn($setup->getTable('quote_address'), 'hrx_parcel_terminal');
        $setup->getConnection()->dropColumn($setup->getTable('sales_order_address'), 'hrx_parcel_terminal');
        $connection->dropTable($setup->getTable('hrx_locations'));
        $connection->dropTable($setup->getTable('hrx_warehouses'));
        $connection->dropTable($setup->getTable('hrx_terminals'));
        $connection->dropTable($setup->getTable('hrx_orders'));
        $setup->endSetup();
    }
}