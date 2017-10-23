<?php
namespace QS\Voucherify\Plugin;

use QS\Voucherify\Api\VoucherDataRepositoryInterface;
use Magento\Quote\Model\QuoteRepository\LoadHandler;

/**
 * Class QuoteLoad
 * @package QS\Voucherify\Plugin
 */
class QuoteLoad
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
     * @param LoadHandler $subject
     * @param \Magento\Quote\Model\Quote $quote
     * @return mixed
     */
    public function afterLoad(
        LoadHandler $subject,
        $quote
    ) {
        $extensionAttributes = $quote->getExtensionAttributes();
        if($extensionAttributes){
            $voucherData = $this->voucherDataRepository->getByQuoteId($quote->getId());

            $quote->setVoucherData($voucherData->getData()); // for direct access on checkout;

            $extensionAttributes->setVoucherCode($voucherData->getVoucherCode());
            $extensionAttributes->setVoucherType($voucherData->getVoucherType());
            $extensionAttributes->setVoucherPercentOff($voucherData->getVoucherPercentOff());
            $extensionAttributes->setVoucherAmountLimit($voucherData->getVoucherAmountLimit());
            $extensionAttributes->setVoucherAmountOff($voucherData->getVoucherAmountOff());
        }
        return $quote;
    }

}