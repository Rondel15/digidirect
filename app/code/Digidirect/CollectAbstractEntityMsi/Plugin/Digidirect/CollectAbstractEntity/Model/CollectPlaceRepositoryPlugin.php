<?php
namespace Digidirect\CollectAbstractEntityMsi\Plugin\Digidirect\CollectAbstractEntity\Model;

use Digidirect\CollectAbstractEntity\Model\CollectPlaceRepository;
use Digidirect\CollectAbstractEntityMsi\Helper\Data;
use Digidirect\CollectAbstractEntityMsi\Model\MsiAvailability;
use Digidirect\Collect\Helper\Data as CollectHelper;

/**
 * Class CollectPlaceRepositoryPlugin
 * @package Digidirect\CollectAbstractEntityMsi\Plugin\Digidirect\CollectAbstractEntity\Model
 */
class CollectPlaceRepositoryPlugin
{
    /**
     * @var MsiAvailability
     */
    protected $msiAvailability;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var CollectHelper
     */
    protected $collectHelper;

    /**
     * CollectPlaceRepositoryPlugin constructor.
     * @param MsiAvailability $msiAvailability
     * @param Data $helper
     * @param CollectHelper $collectHelper
     */
    public function __construct(
        MsiAvailability $msiAvailability,
        Data $helper,
        CollectHelper $collectHelper
    ) {
        $this->msiAvailability = $msiAvailability;
        $this->helper = $helper;
        $this->collectHelper = $collectHelper;
    }

    /**
     * @param CollectPlaceRepository $subject
     * @param array $result Collect Places
     * @param array $places
     * @param array $skus
     * @param int|array $qty
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterFilterListBySkus(
        CollectPlaceRepository $subject,
        $result,
        array $places,
        array $skus,
        $qty = 1
    ) {
        $this->msiAvailability->setAvailabilityForCollectPlaces($result, $skus, $qty);
        return $result;
    }

    /**
     * @param CollectPlaceRepository $subject
     * @param array $result
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetAttributesToLoad(CollectPlaceRepository $subject, $result)
    {
        $msiSourcesCode = $this->helper->getSourceInventoryAttributeFromConfig();
        if ($msiSourcesCode) {
            $result[] = $msiSourcesCode;
        }
        return $result;
    }
}
