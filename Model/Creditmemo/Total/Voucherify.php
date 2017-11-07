<?php
namespace Voucherify\Integration\Model\Creditmemo\Total;

use Magento\Sales\Model\Order\Creditmemo\Total\AbstractTotal;
use Voucherify\Integration\Api\VoucherDataRepositoryInterface;

/**
 * Class Voucherify
 * @package Voucherify\Integration\Model\Creditmemo\Total
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
    public function __construct(VoucherDataRepositoryInterface $voucherDataRepository){
        $this->voucherDataRepository = $voucherDataRepository;
    }

    /**
     * @param \Magento\Sales\Model\Order\Creditmemo $creditmemo
     * @return $this
     */
    public function collect(\Magento\Sales\Model\Order\Creditmemo $creditmemo)
    {
        $quoteId = $creditmemo->getOrder()->getQuoteId();
        $voucherData = $this->voucherDataRepository->getByQuoteId($quoteId);
        if (!is_null($voucherData->getId()) && !is_null($voucherData->getVoucherCode())) {
            foreach ($creditmemo->getOrder()->getCreditmemosCollection() as $prevCreditMemo) {
                $creditmemo->setDiscountAmount(0);
                $creditmemo->setBaseDiscountAmount(0);
                return $this;
            }
            $creditmemo->setDiscountAmount($creditmemo->getOrder()->getBaseDiscountAmount());
            $creditmemo->setBaseDiscountAmount($creditmemo->getOrder()->getDiscountAmount());
            $creditmemo->setDiscountDescription($creditmemo->getOrder()->getDiscountDescription());
            $creditmemo->setGrandTotal($creditmemo->getGrandTotal() + $creditmemo->getOrder()->getDiscountAmount());
            $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() + $creditmemo->getOrder()->getBaseDiscountAmount());
        }
        return $this;
    }
}
