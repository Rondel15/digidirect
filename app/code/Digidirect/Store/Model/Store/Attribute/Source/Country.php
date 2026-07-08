<?php

namespace Digidirect\Store\Model\Store\Attribute\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\App\State;

class Country extends AbstractSource implements OptionSourceInterface
{
    /**
     * @var \Magento\Framework\App\Cache\Type\Config
     */
    protected $configCacheType;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Country factory
     *
     * @var \Magento\Directory\Model\CountryFactory
     */
    protected $countryFactory;

    /**
     * @var State|null
     */
    private $appState;

    /**
     * Country constructor.
     *
     * @param \Magento\Directory\Model\CountryFactory $countryFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Cache\Type\Config $configCacheType
     * @param State|null $state
     */
    public function __construct(
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Cache\Type\Config $configCacheType,
        State $state = null
    ) {
        $this->countryFactory = $countryFactory;
        $this->storeManager = $storeManager;
        $this->configCacheType = $configCacheType;
        $this->appState = $state;
    }

    /**
     * Get list of all available countries
     *
     * @return array
     */
    public function getAllOptions()
    {
        $area = $this->getAppState()->getAreaCode();
        $cacheKey = 'DIGIDIRECT_POS_COUNTRY_SELECT_STORE_' . $this->storeManager->getStore()->getCode() . $area;
        if ($cache = $this->configCacheType->load($cacheKey)) {
            $options = unserialize($cache);
        } else {
            $collection = $this->countryFactory->create()->getResourceCollection()->loadByStore();
            $options = $collection->toOptionArray();
            $this->configCacheType->save(serialize($options), $cacheKey);
        }
        return $options;
    }

    /**
     * @return State|mixed|null
     */
    private function getAppState()
    {
        if (null === $this->appState) {
            $this->appState = ObjectManager::getInstance()->get(\Magento\Framework\App\State::class);
        }
        return $this->appState;
    }
}
