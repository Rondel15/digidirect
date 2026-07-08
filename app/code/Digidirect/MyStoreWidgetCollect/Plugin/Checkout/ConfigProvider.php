<?php

namespace Digidirect\MyStoreWidgetCollect\Plugin\Checkout;

use Magento\Checkout\Model\DefaultConfigProvider;
use Digidirect\MyStoreWidgetCollect\Helper\Config;
use Digidirect\MyStoreWidget\Helper\Data as WidgetStoreHelper;
use Digidirect\MyStoreWidget\Helper\Config as WidgetConfigHelper;
use Digidirect\MyStoreWidget\Model\Config\Source\SearchType;
use Digidirect\Collect\Helper\Data as CollectConfigHelper;

class ConfigProvider
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var WidgetStoreHelper
     */
    private $widgetStoreHelper;

    /**
     * @var WidgetConfigHelper
     */
    private $widgetConfigHelper;

    /**
     * @var CollectConfigHelper
     */
    private $collectConfigHelper;

    /**
     * ConfigProvider constructor.
     * @param WidgetConfigHelper $widgetConfigHelper
     * @param WidgetStoreHelper $widgetStoreHelper
     * @param CollectConfigHelper $collectConfigHelper
     * @param Config $config
     */
    public function __construct(
        WidgetConfigHelper $widgetConfigHelper,
        WidgetStoreHelper $widgetStoreHelper,
        CollectConfigHelper $collectConfigHelper,
        Config $config
    ) {
        $this->config = $config;
        $this->widgetStoreHelper = $widgetStoreHelper;
        $this->widgetConfigHelper = $widgetConfigHelper;
        $this->collectConfigHelper = $collectConfigHelper;
    }

    /**
     * @param DefaultConfigProvider $subject
     * @param array $result
     * @return mixed
     */
    public function afterGetConfig(DefaultConfigProvider $subject, $result)
    {
        if ($this->checkConfig()) {
            $currentStore = $this->widgetStoreHelper->getCurrentStore();
            if (!empty($currentStore) && $currentStore->getId()) {
                $result['quoteData']['selected_collect_place'] = $currentStore->getData();
            }
        }
        return $result;
    }

    /**
     * @return bool
     */
    public function checkConfig()
    {
        $isCCRelation = $this->config->isRelationWithCCEnable();
        $isCorrectSearchType = $this->widgetConfigHelper->getSearchType() === SearchType::AUTOCOMPLETE_TYPE;
        $isSingleCartCC = $this->collectConfigHelper->isSingleCartVariation();
        $isMyStoreEnabled = $this->widgetConfigHelper->isEnable();
        $isDisableNotFullCCVariation = !$this->config->isDisableFromFullCC();
        return $isCCRelation &&
            $isCorrectSearchType &&
            $isSingleCartCC &&
            $isMyStoreEnabled &&
            $isDisableNotFullCCVariation;
    }
}
