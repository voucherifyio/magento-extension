<?php
namespace QS\Voucherify\Model;

use \Magento\Framework\Model\AbstractModel;

/**
 * Class VoucherData
 * @package QS\Voucherify\Model\Quote
 */
class VoucherData extends AbstractModel
{
    /**
     * Set resource model
     */
    protected function _construct(){
        $this->_init('QS\Voucherify\Model\ResourceModel\VoucherData');
    }
}