<?php
namespace Digidirect\CollectStoreLocator\Plugin\Checkout\Model;

use Digidirect\CollectStoreLocator\Helper\Data;
use Digidirect\Collect\Helper\Data as CollectHelper;
use Magento\Checkout\Model\DefaultConfigProvider;

/**
 * Class DefaultConfigProvider
 * @package Digidirect\CollectStoreLocator\Plugin\Checkout\Model
 */
class DefaultConfigProviderPlugin
{
    /**
     * @var Data
     */
    protected $dataHelper;

    /**
     * @var CollectHelper
     */
    protected $collectHelper;

    /**
     * DefaultConfigProvider constructor.
     * @param Data $dataHelper
     * @param CollectHelper $collectHelper
     */
    public function __construct(
        Data $dataHelper,
        CollectHelper $collectHelper
    ) {
        $this->dataHelper = $dataHelper;
        $this->collectHelper = $collectHelper;
    }

    /**
     * @param DefaultConfigProvider $subject
     * @param array $result
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetConfig(DefaultConfigProvider $subject, $result)
    {
        if ($this->collectHelper->isCollectEnable()) {
            $result['quoteData']['locator_block_url'] = $this->dataHelper->getAjaxLocatorBlockUrl();
        }
        return $result;
    }
}
