<?php
namespace QS\Voucherify\Model\Ui\ClientSide;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class ConfigProvider
 */
class ConfigProvider implements ConfigProviderInterface
{
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Retrieve assoc array of voucherify configuration
     *
     * @return array
     */
    public function getConfig()
    {
        return [
            'voucherify' => [
                'clientSide' => [
                    'apiId' => $this->scopeConfig->getValue('qsvoucherify_api/frontend/api_id'),
                    'secretKey' => $this->scopeConfig->getValue('qsvoucherify_api/frontend/secret_key')
                ],
                'behaviour' => [
                    'apply_source_type' => $this->scopeConfig->getValue('qsvoucherify_general/behaviour/apply_source_type')
                ]
            ]
        ];
    }
}
