<?php
namespace QS\Voucherify\Plugin;

use QS\Voucherify\Api\VoucherDataRepositoryInterface;
use \Magento\Quote\Model\QuoteRepository\SaveHandler;

/**
 * Class QuoteSave
 * @package QS\Voucherify\Plugin
 */
class QuoteSave
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
     * @param SaveHandler $subject
     * @param \Magento\Quote\Model\Quote $quote
     * @return mixed
     */
    public function afterSave(
        SaveHandler $subject,
        $quote
    ) {
        $extensionAttributes = $quote->getExtensionAttributes();
        if($extensionAttributes){
            $voucherData = $this->voucherDataRepository->getByQuoteId($quote->getId());
            if ($extensionAttributes->getVoucherCode() != null) {
                $voucherData->setQuoteId($quote->getId());
                $voucherData->setVoucherCode($extensionAttributes->getVoucherCode());
                $voucherData->setVoucherType($extensionAttributes->getVoucherType());
                $voucherData->setVoucherPercentOff($extensionAttributes->getVoucherPercentOff());
                $voucherData->setVoucherAmountLimit($extensionAttributes->getVoucherAmountLimit());
                $voucherData->setVoucherAmountOff($extensionAttributes->getVoucherAmountOff());
                $voucherData->save();
            } else {
                $this->voucherDataRepository->delete($voucherData);
            }
        }
        return $quote;
    }
}