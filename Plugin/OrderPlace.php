<?php
namespace QS\Voucherify\Plugin;

use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Checkout\Api\PaymentInformationManagementInterface;
use QS\Voucherify\Helper\Validator as VoucherValidator;

/**
 * Class OrderPlace
 * @package QS\Voucherify\Plugin
 */
class OrderPlace
{
    /**
     * Quote repository.
     *
     * @var CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var VoucherValidator
     */
    private $validator;

    /**
     * @param CartRepositoryInterface $quoteRepository
     * @param VoucherValidator $validator
     */
    public function __construct(
        CartRepositoryInterface $quoteRepository,
        VoucherValidator $validator
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->validator = $validator;
    }

    /**
     * @param PaymentInformationManagementInterface $subject
     * @param $cartId
     * @param PaymentInterface $paymentMethod
     * @param AddressInterface $billingAddress
     */
    public function beforeSavePaymentInformation(
        PaymentInformationManagementInterface $subject,
        $cartId,
        PaymentInterface $paymentMethod,
        AddressInterface $billingAddress = null
    ){
        /** @var  \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);
        $this->validator->validate($quote);
    }
}