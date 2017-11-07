<?php
namespace Voucherify\Integration\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Voucherify\VoucherifyClient;

/**
 * Class Api
 * @package Voucherify\Integration\Helper
 */
class Api extends  AbstractHelper
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var VoucherifyClient
     */
    private $client = null;

    /**
     * @param Context $context
     * @param ObjectManagerInterface $objectManager
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        ScopeConfigInterface $scopeConfig
    )
    {
        $this->objectManager = $objectManager;
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context);
    }

    /**
     * @return VoucherifyClient
     */
    public function getClient()
    {
        if ($this->client === null) {
            $apiId = $this->scopeConfig->getValue('voucherifyintegration_api/backend/api_id');
            $apiKey = $this->scopeConfig->getValue('voucherifyintegration_api/backend/api_key');
            if ($apiId && $apiKey) {
                $this->client = $this->objectManager->create(VoucherifyClient::class, [
                    'apiId' => $apiId,
                    'apiKey' => $apiKey
                ]);
            }
        }
        return $this->client;
    }
}