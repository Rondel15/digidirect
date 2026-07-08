<?php
namespace Digidirect\ExtendedShippingRates\Ui\DataProvider\Zone\Form\Modifier;

use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Digidirect\ExtendedShippingRates\Model\Zone;
use Digidirect\ExtendedShippingRates\Model\ZoneFactory;

/**
 * Class AbstractModifier
 */
abstract class AbstractModifier implements ModifierInterface
{
    const FORM_NAME = 'digidirect_extendedshippingrates_zone_form';
    const DATA_SOURCE_DEFAULT = 'zone';
    const DATA_SCOPE_METHOD = 'data.zone';

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
     * @var ZoneFactory
     */
    protected $zoneFactory;

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
     * @param ZoneFactory $zoneFactory
     * @param Registry $coreRegistry
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ArrayManager $arrayManager,
        UrlInterface $urlBuilder,
        ZoneFactory $zoneFactory,
        Registry $coreRegistry,
        StoreManagerInterface $storeManager
    ) {
        $this->arrayManager = $arrayManager;
        $this->urlBuilder = $urlBuilder;
        $this->zoneFactory = $zoneFactory;
        $this->registry = $coreRegistry;
        $this->storeManager = $storeManager;
    }

    /**
     * Get current zone or empty
     *
     * @return \Digidirect\ExtendedShippingRates\Model\Zone
     */
    protected function getZone()
    {
        $zone = $this->registry->registry(Zone::CURRENT_ZONE);
        if (!$zone) {
            $zone = $this->zoneFactory->create();
        }

        return $zone;
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
