<?php
namespace QS\Voucherify\Block;

use Magento\Framework\View\Element\Text;
use Magento\Framework\View\Element\Template\Context;
use Magento\Checkout\Model\Session;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use QS\Voucherify\Api\VoucherDataRepositoryInterface;
use QS\Voucherify\Helper\Data as Helper;

/**
 * Class SuccessMessages
 * @package QS\Voucherify\Block
 */
class SuccessMessages extends Text
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $order;

    /**
     * @var PriceHelper
     */
    private $priceHelper;

    /**
     * @var VoucherDataRepositoryInterface
     */
    private $voucherDataRepository;

    /**
     * @var \QS\Voucherify\Helper\Data
     */
    private $helper;

    /**
     * @param Context $context
     * @param Session $checkoutSession
     * @param VoucherDataRepositoryInterface $voucherDataRepository
     * @param ManagerInterface $messageManager
     * @param PriceHelper $priceHelper
     * @param Helper $helper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Session $checkoutSession,
        VoucherDataRepositoryInterface $voucherDataRepository,
        ManagerInterface $messageManager,
        PriceHelper $priceHelper,
        Helper $helper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->checkoutSession = $checkoutSession;
        $this->priceHelper = $priceHelper;
        $this->helper = $helper;
        $this->order = $this->checkoutSession->getLastRealOrder();
        $this->voucherDataRepository = $voucherDataRepository;

        if ($this->checkoutSession->getInvalidatedVoucher()) {
            $messageManager->addNoticeMessage(
                __('Your voucher has not been accepted thus a discount will not be applied.')
            );
        } else {
            $this->addText($this->getDiscountMessage());
        }
        $this->checkoutSession->setInvalidatedVoucher(null);
    }

    /**
     * @return bool|string
     */
    private function getDiscountMessage(){
        $voucherData = $this->voucherDataRepository->getByQuoteId($this->order->getQuoteId());
        if ($voucherData->getId()) {
            $message = 'You redeemed the voucher <b>"' . $voucherData->getVoucherCode() . '"</b>. <br>';
            $message .= 'The old order amount: <b>' . $this->priceHelper->currency($this->helper->getDiscountableAmountByOrder($this->order), true, true) . '</b><br>';
            $message .= 'The discounted order amount: <b>' . $this->priceHelper->currency($this->order->getBaseGrandTotal(), true, true) . '</b>';
            return $message;
        }
        return false;
    }


}