<?php
namespace Voucherify\Integration\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Voucherify\Integration\Model\Config\Source\AmountSourceType;

class Data extends AbstractHelper
{

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ){
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @param null $email
     * @return array
     */
    public function getCustomerDataParams($quote, $email = null)
    {
        $streetArray = $quote->getBillingAddress()->getStreet();
        $streetLine1 = isset($streetArray[0])?$streetArray[0]:null;
        $streetLine2 = isset($streetArray[1])?$streetArray[1]:null;

        if (is_null($email)) {
            $email = $quote->getCustomerEmail();
        }

        return [
            "source_id" => $email,
            "email" => $email,
            "name" => $quote->getBillingAddress()->getFirstname() . ' ' . $quote->getBillingAddress()->getLastname(),
            "phone" => $quote->getBillingAddress()->getTelephone(),
            "address" => [
                "country" => $quote->getBillingAddress()->getCountry(),
                "city" => $quote->getBillingAddress()->getCity(),
                "postal_code" => $quote->getBillingAddress()->getPostcode(),
                "state" => $quote->getBillingAddress()->getRegion(),
                "line_1" => $streetLine1,
                "line_2" => $streetLine2
            ],
            "metadata" => [
                "channel" => "magento"
            ]
        ];
    }

    /**
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return array
     */
    public function getOrderDataParamsFromQuote($quote)
    {
        $items = [];
        foreach ($quote->getAllVisibleItems() as $item) {
            $items[] = [
                "sku_id" => $item->getSku(),
                "quantity" => $item->getQty()
            ];
        }

        $params = [
            "amount" => ($this->getDiscountableAmountByQuote($quote)) * 100,
            "items" => $items,
            "metadata" => [
                "channel" => "magento"
            ]
        ];

        return $params;
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @return array
     */
    public function getOrderDataParams($order)
    {
        $items = [];
        foreach ($order->getAllVisibleItems() as $item) {
            $items[] = [
                "sku_id" => $item->getSku(),
                "quantity" => $item->getQtyOrdered()
            ];
        }

        $params = [
            "amount" => ($this->getDiscountableAmountByOrder($order)) * 100,
            "items" => $items,
            "metadata" => [
                "channel" => "magento",
                "orderId" => $order->getIncrementId()
            ]
        ];

        return $params;
    }


    /**
     * @param $baseSubtotal
     * @param $baseShippingAmount
     * @param $baseTaxAmount
     * @return float
     */
    public function getDiscountableAmount($baseSubtotal, $baseShippingAmount, $baseTaxAmount)
    {
        switch ($this->scopeConfig->getValue('voucherifyintegration_general/behaviour/apply_source_type')) {
            case AmountSourceType::SUBTOTAL :
            default:
                $discountableAmount = $baseSubtotal;
                break;
            case AmountSourceType::SUBTOTAL_SHIPPING :
                $discountableAmount =
                    $baseSubtotal
                    + $baseShippingAmount;
                break;
            case AmountSourceType::SUBTOTAL_TAX :
                $discountableAmount =
                    $baseSubtotal
                    + $baseTaxAmount;
                break;
            case AmountSourceType::SUBTOTAL_SHIPPING_TAX :
                $discountableAmount =
                    $baseSubtotal
                    + $baseShippingAmount
                    + $baseTaxAmount;
                break;
        }
        return $discountableAmount;
    }

    /**
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return mixed
     */
    public function getDiscountableAmountByQuote($quote)
    {
        return $this->getDiscountableAmount(
            $quote->getBaseSubtotal(),
            $quote->getShippingAddress()->getBaseShippingAmount(),
            $quote->getShippingAddress()->getBaseTaxAmount()
        );
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @return mixed
     */
    public function getDiscountableAmountByOrder($order)
    {
        return $this->getDiscountableAmount(
            $order->getBaseSubtotal(),
            $order->getBaseShippingAmount(),
            $order->getBaseTaxAmount()
        );
    }


}