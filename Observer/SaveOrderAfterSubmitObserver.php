<?php
namespace QS\Voucherify\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;
use QS\Voucherify\Helper\Redemption;

/**
 * Class SaveOrderAfterSubmitObserver
 * @package QS\Voucherify\Observer
 */
class SaveOrderAfterSubmitObserver implements ObserverInterface
{

    /**
     * @var
     */
    private $redemption;

    /**
     * @param Redemption $redemption
     */
    public function __construct(
        Redemption $redemption
    ) {
        $this->redemption = $redemption;
    }

    /**
     * @param EventObserver $observer
     * @return $this
     */
    public function execute(EventObserver $observer)
    {
        /* @var $order \Magento\Sales\Model\Order */
        $order = $observer->getEvent()->getData('order');
        /* @var $quote \Magento\Quote\Model\Quote */
        $quote = $observer->getEvent()->getData('quote');
        $extensionAttributes = $quote->getExtensionAttributes();

        if ( $extensionAttributes && !is_null($extensionAttributes->getVoucherCode()) ) {
            $this->redemption->redeemVoucher($quote, $order, $extensionAttributes);
        }

        return $this;
    }

}
