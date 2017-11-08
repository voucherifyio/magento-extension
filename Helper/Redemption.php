<?php
namespace Voucherify\Integration\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Voucherify\Integration\Helper\Api as VoucherifyApi;
use Voucherify\Integration\Helper\Data as Helper;

/**
 * Class Redemption
 * @package Voucherify\Integration\Helper
 */
class Redemption extends AbstractHelper
{

    /**
     * @var \Voucherify\VoucherifyClient
     */
    private $client;

    /**
     * @var \Voucherify\Integration\Helper\Data
     */
    private $helper;

    /**
     * @param Context $context
     * @param Api $api
     * @param Data $helper
     */
    public function __construct(
        Context $context,
        VoucherifyApi $api,
        Helper $helper
    ) {
    
        $this->client = $api->getClient();
        $this->helper = $helper;
        parent::__construct($context);
    }

    /**
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @param \Magento\Quote\Api\Data\CartExtensionInterface $extensionAttributes
     */
    public function redeemVoucher($quote, $order, $extensionAttributes)
    {
        $code = $extensionAttributes->getVoucherCode();
        $params = [
            "customer" => $this->helper->getCustomerDataParams($quote),
            "order" => $this->helper->getOrderDataParams($order),
            "metadata" => [
                "channel" => "magento",
                "orderId" => $order->getIncrementId()
            ]
        ];
        $this->client->redemptions->redeem($code, $params);
    }
}
