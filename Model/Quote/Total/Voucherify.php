<?php
namespace Voucherify\Integration\Model\Quote\Total;

use Magento\Quote\Model\Quote\Address\Total\AbstractTotal;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote\Address\Total as AddressTotal;
use Magento\Quote\Api\Data\CartExtensionInterface;
use Magento\Checkout\Exception;
use Voucherify\Integration\Api\VoucherDataRepositoryInterface;
use Voucherify\Integration\Helper\Data as Helper;
use Voucherify\Integration\Model\VoucherManagement;

/**
 * Class Voucherify
 * @package Voucherify\Integration\Model\Quote\Total
 */
class Voucherify extends AbstractTotal
{

    const CODE = 'voucherify_discount';

    /**
    * @var \Magento\Framework\Pricing\PriceCurrencyInterface
    */
    protected $_priceCurrency;

    /**
     * @var MessageManagerInterface
     */
    protected $messageManager;

    /**
     * @var VoucherDataRepositoryInterface
     */
    private $voucherDataRepository;

    /**
     * @var \Voucherify\Integration\Helper\Data
     */
    private $helper;

    /**
     * @param PriceCurrencyInterface $priceCurrency
     * @param MessageManagerInterface $messageManager
     * @param VoucherDataRepositoryInterface $voucherDataRepository
     * @param Helper $helper
     */
    public function __construct(
        PriceCurrencyInterface $priceCurrency,
        MessageManagerInterface $messageManager,
        VoucherDataRepositoryInterface $voucherDataRepository,
        Helper $helper
    ){
        $this->_priceCurrency = $priceCurrency;
        $this->messageManager = $messageManager;
        $this->voucherDataRepository = $voucherDataRepository;
        $this->helper = $helper;
    }

    /**
     * @param Quote $quote
     * @param ShippingAssignmentInterface $shippingAssignment
     * @param AddressTotal $total
     * @return $this
     * @throws Exception
     * @throws \Exception
     */
    public function collect(
        Quote $quote,
        ShippingAssignmentInterface $shippingAssignment,
        AddressTotal $total
    )
    {
        parent::collect($quote, $shippingAssignment, $total);

        $extensionAttributes = $quote->getExtensionAttributes();
        if($extensionAttributes) {
            if ($extensionAttributes->getVoucherCode() && $total->getTotalAmount('subtotal') > 0) {
                $baseDiscount = $this->getDiscountAmount($extensionAttributes, $quote);
                $discount =  $this->_priceCurrency->convert($baseDiscount);

                $total->addTotalAmount(self::CODE, -$discount);
                $total->addBaseTotalAmount(self::CODE, -$baseDiscount);

                //decline native discount if exists
                $total->addTotalAmount('discount', -($total->getTotalAmount('discount')));
                $total->addBaseTotalAmount('discount', -($total->getBaseTotalAmount('discount')));
                foreach ($quote->getAllItems() as $item) {
                    $item->setDiscountAmount(0);
                    $item->setDiscountPercent(0);
                }

                $total->setDiscountAmount(-$discount);
                $total->setBaseDiscountAmount(-$baseDiscount);
                $total->setSubtotalWithDiscount($total->getSubtotal() - $discount);
                $total->setBaseSubtotalWithDiscount($total->getBaseSubtotal() - $baseDiscount );
            }
        }
        return $this;
    }

    /**
     * @param CartExtensionInterface $extensionAttributes
     * @param Quote $quote
     * @return float
     * @throws Exception
     */
    private function getDiscountAmount(
        CartExtensionInterface $extensionAttributes,
        Quote  $quote)
    {
        $discountableAmount = $this->helper->getDiscountableAmountByQuote($quote);

        if ($extensionAttributes->getVoucherType() == VoucherManagement::VOUCHER_TYPE_AMOUNT) {

            if (!is_numeric($extensionAttributes->getVoucherAmountOff())) {
                throw new Exception(__("The discount value for given voucher is Empty"));
            }
            $voucherAmount = $extensionAttributes->getVoucherAmountOff()/100;
            if ($voucherAmount > $discountableAmount) {
                return $discountableAmount;
            }
            return $voucherAmount;

        } elseif ($extensionAttributes->getVoucherType() == VoucherManagement::VOUCHER_TYPE_PERCENT) {

            if (!is_numeric($extensionAttributes->getVoucherPercentOff())) {
                throw new Exception(__("The discount value for given voucher is Empty"));
            }
            $discount = $discountableAmount / 100 * $extensionAttributes->getVoucherPercentOff();
            if (!is_null($quote->getExtensionAttributes()->getVoucherAmountLimit())) {
                $limit = $quote->getExtensionAttributes()->getVoucherAmountLimit()/100;
                $discount = ($limit<$discount)?$limit:$discount;
            }
            return $discount;

        } elseif ($extensionAttributes->getVoucherType() == VoucherManagement::VOUCHER_TYPE_GIFT) {
            if (!is_numeric($extensionAttributes->getVoucherAmountOff())) {
                throw new Exception(__("The discount value for given voucher is Empty"));
            }

            $giftAmount = $extensionAttributes->getVoucherAmountOff()/100;
            if ($giftAmount > $discountableAmount) {
                return $discountableAmount;
            }
            return $extensionAttributes->getVoucherAmountOff()/100;
        } elseif ($extensionAttributes->getVoucherType() == VoucherManagement::VOUCHER_TYPE_UNIT) {
            throw new Exception(__("Unit discount type is not supported"));
        }  else {
            throw new Exception(__("Empty discount type"));
        }
    }
}