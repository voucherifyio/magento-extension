<?php
namespace Voucherify\Integration\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;

/**
 * Class VoucherData
 * @package Voucherify\Integration\Model\ResourceModel\Quote
 */
class VoucherData extends AbstractDb
{
    /**
     * @param Context $context
     * @param null $connectionName
     */
    public function __construct(
        Context $context,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
    }

    /**
     * set table info
     */
    protected function _construct()
    {
        $this->_init('voucherify_integration_quote_data', 'entity_id');
    }
}
