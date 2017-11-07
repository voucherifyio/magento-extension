<?php
namespace Voucherify\Integration\Plugin;

use Voucherify\Integration\Api\VoucherDataRepositoryInterface;
use Magento\Sales\Model\OrderRepository;
use Magento\Sales\Model\Order;

/**
 * Class OrderGet
 * @package Voucherify\Integration\Plugin
 */
class OrderGet
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
     * @param OrderRepository $subject
     * @param Order $order
     * @return Order
     */
    public function afterGet(
        OrderRepository $subject,
        Order $order
    ) {
        $quoteId = $order->getQuoteId();
        $voucherData = $this->voucherDataRepository->getByQuoteId($quoteId);
        if (!is_null($voucherData->getId()) && !is_null($voucherData->getVoucherCode())) {
            $order->setDiscountDescription($voucherData->getVoucherCode());
        }
        return $order;
    }

}