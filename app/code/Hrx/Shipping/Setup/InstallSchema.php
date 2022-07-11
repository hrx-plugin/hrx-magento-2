<?php

namespace Hrx\Shipping\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        if ($installer->getConnection()->tableColumnExists('quote_address', 'hrx_parcel_terminal') === false) {
            $installer->getConnection()->addColumn(
                $installer->getTable('quote_address'),
                'hrx_parcel_terminal',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'nullable' => true,
                    'comment' => 'Hrx Parcel Terminal',
                ]
            );
        }

        if ($installer->getConnection()->tableColumnExists('sales_order_address', 'hrx_parcel_terminal') === false) {
            $installer->getConnection()->addColumn(
                $installer->getTable('sales_order_address'),
                'hrx_parcel_terminal',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'nullable' => true,
                    'comment' => 'Hrx Parcel Terminal',
                ]
            );
        }

        if (!$installer->tableExists('hrx_terminals')) {
            $table = $installer->getConnection()->newTable(
                            $installer->getTable('hrx_terminals')
                    )
                    ->addColumn(
                            'id',
                            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                            null,
                            [
                                'identity' => true,
                                'nullable' => false,
                                'primary' => true,
                                'unsigned' => true,
                            ],
                            'ID'
                    )
                    ->addColumn(
                            'terminal_id',
                            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                            100,
                            ['nullable => true'],
                            'Terminal id'
                    )
                    ->addColumn(
                            'city',
                            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                            255,
                            ['nullable => true'],
                            'City'
                    )
                    ->addColumn(
                            'address',
                            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                            255,
                            ['nullable => true'],
                            'Address'
                    )
                    ->addColumn(
                            'postcode',
                            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                            20,
                            ['nullable => true'],
                            'Postcode'
                    )
                    ->addColumn(
                            'country',
                            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                            2,
                            ['nullable => true'],
                            'Country'
                    )
                    ->addColumn(
                            'latitude',
                            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                            255,
                            ['nullable => true'],
                            'Latitude'
                    )
                    ->addColumn(
                            'longitude',
                            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                            255,
                            ['nullable => true'],
                            'Longitude'
                    )
                    ->addColumn(
                            'phone_prefix',
                            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                            10,
                            ['nullable => true'],
                            'Phone prefix'
                    )
                    ->addColumn(
                            'phone_regex',
                            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                            20,
                            ['nullable => true'],
                            'Phone regex'
                    )
                    ->addColumn(
                            'min_length',
                            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                            '10,2',
                            ['nullable => true'],
                            'Min length'
                    )
                    ->addColumn(
                            'max_length',
                            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                            '10,2',
                            ['nullable => true'],
                            'Max length'
                    )
                    ->addColumn(
                            'min_height',
                            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                            '10,2',
                            ['nullable => true'],
                            'Min height'
                    )
                    ->addColumn(
                            'max_height',
                            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                            '10,2',
                            ['nullable => true'],
                            'Max height'
                    )
                    ->addColumn(
                            'min_width',
                            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                            '10,2',
                            ['nullable => true'],
                            'Min width'
                    )
                    ->addColumn(
                            'max_width',
                            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                            '10,2',
                            ['nullable => true'],
                            'Max width'
                    )
                    ->addColumn(
                            'min_weight',
                            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                            '10,2',
                            ['nullable => true'],
                            'Min weight'
                    )
                    ->addColumn(
                            'max_weight',
                            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                            '10,2',
                            ['nullable => true'],
                            'Max weight'
                    )
                    ->addColumn(
                            'active',
                            \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
                            1,
                            ['default' => 0],
                            'Active'
                    )
                    ->addColumn(
                            'created_at',
                            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                            null,
                            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                            'Created At'
                    )->addColumn(
                            'updated_at',
                            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                            null,
                            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
                            'Updated At')
                    ->setComment('HRX terminals');
            $installer->getConnection()->createTable($table);

        }

        if (!$installer->tableExists('hrx_warehouses')) {
            $table = $installer->getConnection()->newTable(
                            $installer->getTable('hrx_warehouses')
                    )
                    ->addColumn(
                            'id',
                            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                            null,
                            [
                                'identity' => true,
                                'nullable' => false,
                                'primary' => true,
                                'unsigned' => true,
                            ],
                            'ID'
                    )
                    ->addColumn(
                            'warehouse_id',
                            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                            100,
                            ['nullable => true'],
                            'Warehouse id'
                    )
                    ->addColumn(
                            'name',
                            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                            255,
                            ['nullable => true'],
                            'Name'
                    )
                    ->addColumn(
                            'city',
                            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                            255,
                            ['nullable => true'],
                            'City'
                    )
                    ->addColumn(
                            'address',
                            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                            255,
                            ['nullable => true'],
                            'Address'
                    )
                    ->addColumn(
                            'postcode',
                            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                            20,
                            ['nullable => true'],
                            'Postcode'
                    )
                    ->addColumn(
                            'country',
                            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                            2,
                            ['nullable => true'],
                            'Country'
                    )
                    ->addColumn(
                            'active',
                            \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
                            1,
                            ['default' => 0],
                            'Active'
                    )
                    ->addColumn(
                            'created_at',
                            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                            null,
                            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                            'Created At'
                    )->addColumn(
                            'updated_at',
                            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                            null,
                            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
                            'Updated At')
                    ->setComment('HRX terminals');
            $installer->getConnection()->createTable($table);

        }

        if (!$installer->tableExists('hrx_orders')) {
            $table = $installer->getConnection()->newTable(
                            $installer->getTable('hrx_orders')
                    )
                    ->addColumn(
                            'id',
                            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                            null,
                            [
                                'identity' => true,
                                'nullable' => false,
                                'primary' => true,
                                'unsigned' => true,
                            ],
                            'ID'
                    )
                    ->addColumn(
                            'order_id',
                            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                            100,
                            ['nullable => true'],
                            'Order id'
                    )
                    ->addColumn(
                            'hrx_terminal_id',
                            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                            null,
                            ['nullable => true'],
                            'Terminal id'
                    )
                    ->addColumn(
                            'hrx_warehouse_id',
                            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                            null,
                            ['nullable => true'],
                            'Warehouse id'
                    )
                    ->addColumn(
                            'shop_order_id',
                            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                            null,
                            ['nullable => true'],
                            'Shop order id'
                    )
                    ->addColumn(
                            'status',
                            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                            255,
                            ['nullable => true'],
                            'Status'
                    )
                    ->addColumn(
                            'tracking',
                            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                            255,
                            ['nullable => true'],
                            'Tracking'
                    )
                    ->addColumn(
                            'warehouse_name',
                            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                            255,
                            ['nullable => true'],
                            'Warehouse name'
                    )
                    ->addColumn(
                            'terminal_name',
                            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                            255,
                            ['nullable => true'],
                            'Terminal name'
                    )
                    ->addColumn(
                            'length',
                            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                            '10,2',
                            ['nullable => true'],
                            'Length'
                    )
                    ->addColumn(
                            'height',
                            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                            '10,2',
                            ['nullable => true'],
                            'Height'
                    )
                    ->addColumn(
                            'width',
                            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                            '10,2',
                            ['nullable => true'],
                            'Width'
                    )
                    ->addColumn(
                            'weight',
                            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                            '10,3',
                            ['nullable => true'],
                            'Weight'
                    )
                    ->addColumn(
                            'quantity',
                            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                            null,
                            ['nullable => true'],
                            'Quantity'
                    )
                    ->addColumn(
                            'created_at',
                            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                            null,
                            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                            'Created At'
                    )->addColumn(
                            'updated_at',
                            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                            null,
                            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
                            'Updated At')
                    ->setComment('HRX terminals');
            $installer->getConnection()->createTable($table);

        }
         

        $setup->endSetup();
    }
}