<?php
namespace Digidirect\ExtendedCatalogPriceRule\Plugin\Magento\ConfigurableProduct\Block\Product\View\Type;

use Digidirect\ExtendedCatalogPriceRule\Api\Data\RuleDisplayMessageInterface;
use Digidirect\ExtendedCatalogPriceRule\Helper\ViewData;
use Magento\ConfigurableProduct\Block\Product\View\Type\Configurable;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * Class ConfigurablePlugin
 * @package Digidirect\ExtendedCatalogPriceRule\Plugin\Magento\ConfigurableProduct\Block\Product\View\Type
 */
class ConfigurablePlugin
{
    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @var ViewData
     */
    protected $helper;

    /**
     * ConfigurablePlugin constructor.
     * @param ViewData $helper
     * @param SerializerInterface $serializer
     */
    public function __construct(
        ViewData $helper,
        SerializerInterface $serializer
    ) {
        $this->helper = $helper;
        $this->serializer = $serializer;
    }

    /**
     * @param Configurable $subject
     * @param string $result
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetJsonConfig(Configurable $subject, $result)
    {
        $decodedConfig = $this->serializer->unserialize($result);
        $productIds = array_keys($decodedConfig['index']);

        $rulesData = $this->helper->getExtendedRulesDataForView($productIds);
        if (empty($rulesData)) {
            return $result;
        }

        $decodedConfig['extended_rules'] = $rulesData;
        $forParent = $this->collectGivenExtendedRulesForParent($rulesData);
        if ($forParent && ($parentId = $decodedConfig['productId'] ?? null)) {
            $decodedConfig['extended_rules'][$parentId] = $forParent;
        }

        return $this->serializer->serialize($decodedConfig);
    }

    /**
     * @param array $rulesData
     * @return array|bool
     */
    protected function collectGivenExtendedRulesForParent(array $rulesData)
    {
        // Count all extended rules for children.
        $ruleIdsCounter = [];
        $rulesDataForParent = [];
        foreach ($rulesData as $productId => $data) {
            foreach ($data as $key => $value) {
                $ruleIdsCounter[$key] = ($ruleIdsCounter[$key] ?? null) ? ++$ruleIdsCounter[$key] : 1;

                if (empty($rulesDataForParent[$key])) {
                    $rulesDataForParent[$key] = $value;
                }
            }
        }

        return $rulesDataForParent;
    }
}
