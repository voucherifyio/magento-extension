<?php
namespace Voucherify\Integration\Model\Config\Source;

use \Magento\Framework\Option\ArrayInterface;

/**
 * Class AmountSourceType
 * @package Voucherify\Integration\Model\Config\Source
 */
class AmountSourceType implements ArrayInterface
{

    const SUBTOTAL = 'subtotal';
    const SUBTOTAL_SHIPPING = 'including_shipping';
    const SUBTOTAL_TAX = 'including_tax';
    const SUBTOTAL_SHIPPING_TAX = 'including_shipping_tax';

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::SUBTOTAL,
                'label' => 'Subtotal'
            ],
            [
                'value' => self::SUBTOTAL_SHIPPING,
                'label' => 'Subtotal including shipping'
            ],
            [
                'value' => self::SUBTOTAL_TAX,
                'label' => 'Subtotal including taxes'
            ],
            [
                'value' => self::SUBTOTAL_SHIPPING_TAX,
                'label' => 'Subtotal including shipping and taxes'
            ]
        ];
    }
}