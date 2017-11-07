<?php
namespace Voucherify\Integration\Helper;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Voucherify\Integration\Helper\Api as VoucherifyApi;
use Voucherify\Integration\Helper\Data as Helper;
use Voucherify\Integration\Model\VoucherManagement;

/**
 * Class Validator
 * @package Voucherify\Integration\Helper
 */
class Validator extends AbstractHelper
{

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Voucherify\VoucherifyClient
     */
    private $client;

    /**
     * @var \Voucherify\Integration\Helper\Data
     */
    private $helper;

    /**
     * Quote repository.
     *
     * @var CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    private $guestEmail = null;


    /**
     * @param Context $context
     * @param CartRepositoryInterface $quoteRepository
     * @param ScopeConfigInterface $scopeConfig
     * @param Api $api
     * @param CheckoutSession $checkoutSession
     * @param Data $helper
     */
    public function __construct(
        Context $context,
        CartRepositoryInterface $quoteRepository,
        ScopeConfigInterface $scopeConfig,
        VoucherifyApi $api,
        CheckoutSession $checkoutSession,
        Helper $helper
    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->client = $api->getClient();
        $this->quoteRepository = $quoteRepository;
        $this->_checkoutSession = $checkoutSession;
        $this->helper = $helper;
        parent::__construct($context);
    }

    /**
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @param null $email
     * @throws CouldNotSaveException
     */
    public function validate($quote, $email = null)
    {

        if (!is_null($email)) {
            $this->guestEmail = $email;
        }

        $extensionAttributes = $quote->getExtensionAttributes();
        if ( $extensionAttributes && !is_null($extensionAttributes->getVoucherCode()) ) {
            $voucherCode = $extensionAttributes->getVoucherCode();
            $validated = $this->check($voucherCode,
                $quote,
                $extensionAttributes->getVoucherType(),
                $extensionAttributes->getVoucherPercentOff(),
                $extensionAttributes->getVoucherAmountOff()
            );
            if (!$validated) {
                $quote->getExtensionAttributes()->setVoucherCode(null);
                $quote->getExtensionAttributes()->setVoucherType(null);
                $quote->getExtensionAttributes()->setVoucherAmountOff(null);
                $quote->getExtensionAttributes()->setVoucherAmountLimit(null);
                $quote->getExtensionAttributes()->setVoucherPercentOff(null);
                $this->quoteRepository->save($quote->collectTotals());

                if ($this->scopeConfig->getValue('voucherifyintegration_general/behaviour/prevent_order_creating')) {
                    throw new CouldNotSaveException(__("Coupon code is no longer available"), null, 45689);
                } else {
                    $this->_checkoutSession->setInvalidatedVoucher($voucherCode);
                }
            }
        }
    }

    /**
     * @param $voucherCode
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @param $type
     * @param null $percentOff
     * @param null $amountOff
     * @return bool
     */
    private function check($voucherCode, $quote, $type, $percentOff = null, $amountOff = null)
    {
        $params = $this->generateParams($quote);
        $response = $this->client->validations->validateVoucher($voucherCode, $params);
        if (!$response->valid)
            return false;

        if (isset($response->gift)) {
            if ((VoucherManagement::VOUCHER_TYPE_GIFT != $type) ||
                (!isset($response->gift->amount) || $amountOff != $response->gift->balance)
            ) return false;
        } elseif (isset($response->discount)) {
            if (($response->discount->type != $type) ||
                (!is_null($amountOff) && (!isset($response->discount->amount_off) || $amountOff != $response->discount->amount_off)) ||
                (!is_null($percentOff) && (!isset($response->discount->percent_off) || $percentOff != $response->discount->percent_off))
            ) return false;
        } else {
            return false;
        }

        return true;
    }

    /**
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return array
     */
    private function generateParams($quote)
    {
        return [
            "customer" => $this->helper->getCustomerDataParams($quote, $this->guestEmail),
            "order" => $this->helper->getOrderDataParamsFromQuote($quote),
            "metadata" => [
                "channel" => "magento"
            ]
        ];
    }

}