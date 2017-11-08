<?php
namespace Voucherify\Integration\Model;

use \Magento\Framework\Model\AbstractModel;

/**
 * Class VoucherData
 * @package Voucherify\Integration\Model\Quote
 */
class VoucherData extends AbstractModel
{
    /**
     * Set resource model
     */
    protected function _construct()
    {
        $this->_init('Voucherify\Integration\Model\ResourceModel\VoucherData');
    }
}
