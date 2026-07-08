<?php
namespace Digidirect\CheckoutFields\Model\Checkout\Provider\CustomCheckoutFields;

use Magento\Checkout\Model\ConfigProviderInterface;

/**
 * Class CompositeConfigProvider
 *
 * @package Digidirect\CheckoutFields\Model\Checkout\Provider\CustomCheckoutFields
 */
class CompositeConfigProvider implements ConfigProviderInterface
{
    /**
     * @var ConfigProviderInterface[]
     */
    private $configProviders;

    /**
     * @param ConfigProviderInterface[] $configProviders
     * @codeCoverageIgnore
     */
    public function __construct(
        array $configProviders = []
    ) {
        $this->configProviders = $configProviders;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        $config = [];
        foreach ($this->configProviders as $configProvider) {
            $config = array_merge_recursive($config, $configProvider->getConfig());
        }
        return $config;
    }
}
