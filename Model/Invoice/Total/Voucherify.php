<?php
namespace Voucherify\Integration\Model\Invoice\Total;

use Magento\Sales\Model\Order\Invoice\Total\AbstractTotal;
use Voucherify\Integration\Api\VoucherDataRepositoryInterface;

/**
 * Class Voucherify
 * @package Voucherify\Integration\Model\Invoice\Total
 */
class Voucherify extends AbstractTotal
{

    /**
     * @var VoucherDataRepositoryInterface
     */
    private $voucherDataRepository;

    /**
     * @param VoucherDataRepositoryInterface $voucherDataRepository
     */
    public function __construct(VoucherDataRepositoryInterface $voucherDataRepository)
    {
        $this->voucherDataRepository = $voucherDataRepository;
    }

    /**
     * @param \Magento\Sales\Model\Order\Invoice $invoice
     * @return $this
     */
    public function collect(\Magento\Sales\Model\Order\Invoice $invoice)
    {
        $quoteId = $invoice->getOrder()->getQuoteId();
        $voucherData = $this->voucherDataRepository->getByQuoteId($quoteId);
        if (!is_null($voucherData->getId()) && !is_null($voucherData->getVoucherCode())) {
            $invoice->setDiscountAmount($invoice->getOrder()->getBaseDiscountAmount());
            $invoice->setBaseDiscountAmount($invoice->getOrder()->getDiscountAmount());
            $invoice->setDiscountDescription($invoice->getOrder()->getDiscountDescription());
            $invoice->setGrandTotal($invoice->getGrandTotal() + $invoice->getOrder()->getDiscountAmount());
            $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $invoice->getOrder()->getBaseDiscountAmount());
        }
        return $this;
    }
}
