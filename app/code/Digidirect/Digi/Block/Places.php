<?php

namespace Digidirect\Digi\Block;

use \Magento\Framework\View\Element\Template;
use \Magento\Framework\View\Element\Template\Context;
use \Magento\Framework\Registry;
use \Digidirect\CollectAbstractEntity\Helper\Places as PlacesHelper;
use \Digidirect\Collect\Api\CollectPlaceRepositoryInterface;

/**
 * Class Fields
 * @package Digidirect\CheckoutFields\Block\Pdp\Custom
 */
class Places extends Template
{

    private $placeHelper = null;
    /**
     * @var Registry
     */
    private $registry;

    /**
     * Places constructor.
     * @param PlacesHelper $placesHelper
     * @param Context $context
     * @param array $data
     */

    public function __construct(
        PlacesHelper $placesHelper,
        Registry $registry,
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->placeHelper = $placesHelper;
        $this->registry = $registry;
    }

    /**
     * @param $product
     * @return \Digidirect\Collect\Api\Data\CollectPlaceInterface[]
     */
    public function getAllCollectPlaces($product)
    {
        $result = [];
        $places = $this->placeHelper->getAllCollectPlacesEntities([$product->getSku() => 1]);

        foreach ($places as $place) {
            if ($place->hasData(CollectPlaceRepositoryInterface::KEY_IS_UNAVAILABLE)) {
                $result[$place->getName()] = [
                    'available' =>!$place->getData(CollectPlaceRepositoryInterface::KEY_IS_UNAVAILABLE),
                    'place_object' => $place
                ];
            }
        }
        return $result;
    }

    /**
     * @return mixed
     */
    public function getProduct()
    {
        return $this->registry->registry('current_product');
    }
}
