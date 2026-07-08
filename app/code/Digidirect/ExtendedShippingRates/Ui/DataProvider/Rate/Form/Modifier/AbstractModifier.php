<?php
namespace Digidirect\ExtendedShippingRates\Ui\DataProvider\Rate\Form\Modifier;

use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Digidirect\ExtendedShippingRates\Model\Carrier\Method\Rate;
use Digidirect\ExtendedShippingRates\Model\Carrier\Method\RateFactory;

/**
 * Class AbstractModifier
 */
abstract class AbstractModifier implements ModifierInterface
{
    const FORM_NAME = 'digidirect_extendedshippingrates_rate_form';
    const DATA_SOURCE_DEFAULT = 'rate';
    const DATA_SCOPE_METHOD = 'data.rate';

    /**
     * Container fieldset prefix
     */
    const CONTAINER_PREFIX = 'container_';

    /**
     * Meta config path
     */
    const META_CONFIG_PATH = '/arguments/data/config';

    /**
     * @var ArrayManager
     */
    protected $arrayManager;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var RateFactory
     */
    protected $rateFactory;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param ArrayManager $arrayManager
     * @param UrlInterface $urlBuilder
     * @param RateFactory $rateFactory
     * @param Registry $coreRegistry
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ArrayManager $arrayManager,
        UrlInterface $urlBuilder,
        RateFactory $rateFactory,
        Registry $coreRegistry,
        StoreManagerInterface $storeManager
    ) {
        $this->arrayManager = $arrayManager;
        $this->urlBuilder = $urlBuilder;
        $this->rateFactory = $rateFactory;
        $this->registry = $coreRegistry;
        $this->storeManager = $storeManager;
    }

    /**
     * Get current method or empty
     *
     * @return \Digidirect\ExtendedShippingRates\Model\Carrier\Method\Rate
     */
    protected function getRate()
    {
        $rate = $this->registry->registry(Rate::CURRENT_RATE);
        if (!$rate) {
            $rate = $this->rateFactory->create();
        }

        return $rate;
    }

    /**
     * Get currency symbol
     *
     * @return string
     */
    protected function getBaseCurrencySymbol()
    {
        return $this->storeManager->getStore()->getBaseCurrency()->getCurrencySymbol();
    }
}
