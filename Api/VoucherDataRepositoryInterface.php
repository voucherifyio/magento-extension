<?php
namespace Voucherify\Integration\Api;

use Voucherify\Integration\Model\VoucherData;

/**
 * Interface VoucherDataRepositoryInterface
 * @package Voucherify\Integration\Api
 */
interface VoucherDataRepositoryInterface
{
    /**
     * @param int $voucherId
     * @return mixed
     */
    public function getById($voucherId);

    /**
     * @param int $quoteId
     * @return mixed
     */
    public function getByQuoteId($quoteId);

    /**
     * @param VoucherData $voucherData
     * @return mixed
     */
    public function save(VoucherData $voucherData);


    /**
     * @param VoucherData $voucherData
     * @return mixed
     */
    public function delete(VoucherData $voucherData);

    /**
     * @param int $voucherId
     * @return mixed
     */
    public function deleteById($voucherId);

    /**
     * @param int $quoteId
     * @return mixed
     */
    public function deleteByQuoteId($quoteId);
}
