<?php
namespace QS\VOucherify\Api;

/**
 * Voucher management service interface.
 * @api
 */
interface VoucherManagementInterface
{
    /**
     * @param string $cartId The cart ID.
     * @return string The voucher code data.
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified cart does not exist.
     */
    public function get($cartId);

    /**
     * @param string $cartId The cart ID.
     * @param string $voucherCode The voucher code data.
     * @param string $type.
     * @param int $percent_off.
     * @param int $amount_limit.
     * @param int $amount_off.
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified cart does not exist.
     * @throws \Magento\Framework\Exception\CouldNotSaveException The specified voucher could not be added.
     */
    public function set($cartId, $voucherCode, $type, $percent_off = null, $amount_limit = null, $amount_off = null);

    /**
     * @param string $cartId The cart ID.
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified cart does not exist.
     * @throws \Magento\Framework\Exception\CouldNotDeleteException The specified voucher could not be deleted.
     */
    public function remove($cartId);
}
