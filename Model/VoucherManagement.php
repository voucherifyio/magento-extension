<?php

namespace Voucherify\Integration\Model;

use \Voucherify\Integration\Api\VoucherManagementInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Quote\Api\CartRepositoryInterface;

/**
 * Voucher management object.
 */
class VoucherManagement implements VoucherManagementInterface
{

    const VOUCHER_TYPE_PERCENT = "PERCENT";
    const VOUCHER_TYPE_AMOUNT = "AMOUNT";
    const VOUCHER_TYPE_GIFT = "GIFT";
    const VOUCHER_TYPE_UNIT = "UNIT";

    /**
     * Quote repository.
     *
     * @var CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var QuoteIdMaskFactory
     */
    private $quoteIdMaskFactory;

    /**
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     * @param CartRepositoryInterface $quoteRepository
     */
    public function __construct(
        QuoteIdMaskFactory $quoteIdMaskFactory,
        CartRepositoryInterface $quoteRepository
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
    }

    /**
     * @param $cartId
     * @return integer
     */
    private function getRealCartId($cartId)
    {
        if (!is_numeric($cartId)) {
            $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
            return $quoteIdMask->getQuoteId();
        }
        return $cartId;
    }

    /**
     * {@inheritdoc}
     */
    public function get($cartId)
    {
        $cartId = $this->getRealCartId($cartId);
        /** @var  \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);
        return [
            'voucher_code' => $quote->getExtensionAttributes()->getVoucherCode(),
            'voucher_type' => $quote->getExtensionAttributes()->getVoucherType(),
            'voucher_percent_off' => $quote->getExtensionAttributes()->getVoucherPercentOff(),
            'voucher_amount_limit' => $quote->getExtensionAttributes()->getVoucherAmountLimit(),
            'voucher_amount_off' => $quote->getExtensionAttributes()->getVoucherAmountOff()
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function set($cartId, $voucherCode, $type, $percent_off = null, $amount_limit = null, $amount_off = null)
    {
        $cartId = $this->getRealCartId($cartId);
        /** @var  \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);
        if (!$quote->getItemsCount()) {
            throw new NoSuchEntityException(__('Cart %1 doesn\'t contain products', $cartId));
        }
        $quote->getShippingAddress()->setCollectShippingRates(true);

        try {
            $quote->getExtensionAttributes()->setVoucherCode($voucherCode);
            $quote->getExtensionAttributes()->setVoucherType($type);
            $quote->getExtensionAttributes()->setVoucherPercentOff($percent_off);
            $quote->getExtensionAttributes()->setVoucherAmountLimit($amount_limit);
            $quote->getExtensionAttributes()->setVoucherAmountOff($amount_off);
            $this->quoteRepository->save($quote->collectTotals());
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__($e->getMessage()));
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function remove($cartId)
    {
        $cartId = $this->getRealCartId($cartId);
        /** @var  \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);
        if (!$quote->getItemsCount()) {
            throw new NoSuchEntityException(__('Cart %1 doesn\'t contain products', $cartId));
        }
        $quote->getShippingAddress()->setCollectShippingRates(true);

        try {
            $quote->getExtensionAttributes()->setVoucherCode(null);
            $quote->getExtensionAttributes()->setVoucherType(null);
            $quote->getExtensionAttributes()->setVoucherPercentOff(null);
            $quote->getExtensionAttributes()->setVoucherAmountOff(null);

            $this->quoteRepository->save($quote->collectTotals());
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(__('Could not cancel voucher'));
        }
        return true;
    }
}
