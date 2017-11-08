<?php
namespace Voucherify\Integration\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Class InstallSchema
 * @package Voucherify\Integration\Setup
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws \Zend_Db_Exception
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        $tableName = 'voucherify_integration_quote_data';
        $installer->getConnection()->dropTable($installer->getTable($tableName));

        $table = $installer->getConnection()->newTable(
            $installer->getTable($tableName)
        )->addColumn(
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            10,
            ['identity' => true, 'nullable' => false, 'primary' => true],
            'Entity Id'
        )->addColumn(
            'quote_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Quote Id'
        )->addColumn(
            'voucher_code',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Voucher Code'
        )->addColumn(
            'voucher_type',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Voucher Type'
        )->addColumn(
            'voucher_percent_off',
            \Magento\Framework\DB\Ddl\Table::TYPE_FLOAT,
            null,
            ['nullable' => true],
            'Voucher Percent Off'
        )->addColumn(
            'voucher_amount_limit',
            \Magento\Framework\DB\Ddl\Table::TYPE_FLOAT,
            null,
            ['nullable' => true],
            'Voucher Upper Discountable Limit'
        )->addColumn(
            'voucher_amount_off',
            \Magento\Framework\DB\Ddl\Table::TYPE_FLOAT,
            null,
            ['nullable' => true],
            'Voucher Amount Off'
        )->addIndex(
            $installer->getIdxName('voucherify_integration_quote_data', ['quote_id']),
            ['quote_id']
        )->addForeignKey(
            $installer->getFkName('voucherify_integration_quote_data', 'quote_id', 'quote', 'entity_id'),
            'quote_id',
            $installer->getTable('quote'),
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        );

        $installer->getConnection()->createTable($table);
        $installer->endSetup();
    }
}
