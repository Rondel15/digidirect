<?php
namespace Digidirect\ExtendedShippingRates\Block\Adminhtml\ExtendedShippingRates\Zone\Edit\Button;

use Magento\Framework\Registry;
use Digidirect\ExtendedShippingRates\Model\ZoneFactory;
use Magento\Framework\View\Element\UiComponent\Context;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Digidirect\ExtendedShippingRates\Model\Zone;

class Generic implements ButtonProviderInterface
{
    /**
     * Url Builder
     *
     * @var Context
     */
    protected $context;

    /**
     * Registry
     *
     * @var Registry
     */
    protected $registry;

    /**
     * @var ZoneFactory
     */
    protected $zoneFactory;

    /**
     * Generic constructor
     *
     * @param Context $context
     * @param Registry $registry
     * @param ZoneFactory $zoneFactory
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ZoneFactory $zoneFactory
    ) {
        $this->context = $context;
        $this->registry = $registry;
        $this->zoneFactory = $zoneFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getButtonData()
    {
        return [];
    }

    /**
     * Get zone: current or empty
     *
     * @return \Digidirect\ExtendedShippingRates\Model\Zone
     */
    public function getZone()
    {
        $zone = $this->registry->registry(Zone::CURRENT_ZONE);
        if (!$zone) {
            $zone = $this->zoneFactory->create();
        }

        return $zone;
    }
}
