<?php
namespace Digidirect\LayeredNavigation\Model\Layer;

use Magento\Directory\Model\PriceCurrency as MagentoPriceCurrency;
use Digidirect\LayeredNavigation\Api\Data\FilterSettingInterface;

class PriceCurrency extends MagentoPriceCurrency
{
    /**
     * @param float|int $amount
     * @param FilterSettingInterface|null $settings
     * @return string
     */
    public function formatLayerPrice($amount, FilterSettingInterface $settings = null)
    {
        if ($settings === null || $settings->getUnitsLabelUseCurrencySymbol()) {
            return $this->format($amount);
        }
        return round($amount, 4) . ' ' . $settings->getUnitsLabel();
    }

    /**
     * @param float|string $fromPrice
     * @param float|string $toPrice
     * @param FilterSettingInterface $filterSetting
     * @return float|\Magento\Framework\Phrase
     */
    public function renderRangeLabel($fromPrice, $toPrice, FilterSettingInterface $filterSetting)
    {
        $formattedFromPrice = $this->formatLayerPrice($fromPrice, $filterSetting);
        if ($toPrice === '') {
            return __('%1 and above', $formattedFromPrice);
        } else if ($fromPrice >= $toPrice) {
            return __('%1', $formattedFromPrice);
        } else {
            if ($fromPrice != $toPrice) {
                $toPrice -= .01;
            }

            $formattedToPrice = $this->formatLayerPrice($toPrice, $filterSetting);
            return __('%1 - %2', $formattedFromPrice, $formattedToPrice);
        }
    }
}
