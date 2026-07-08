<?php
namespace Digidirect\CollectAbstractEntity\Helper;

use Digidirect\CollectAbstractEntity\Model\CollectPlaceRepository;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

/**
 * Class Places
 * @package Digidirect\CollectAbstractEntity\Helper
 */
class Places extends AbstractHelper
{
    /**
     * @var CollectPlaceRepository
     */
    protected $collectPlaceRepository;

    /**
     * Places constructor.
     * @param Context $context
     * @param CollectPlaceRepository $collectPlaceRepository
     */
    public function __construct(
        Context $context,
        CollectPlaceRepository $collectPlaceRepository
    ) {
        parent::__construct($context);
        $this->collectPlaceRepository = $collectPlaceRepository;
    }

    /**
     * @param array $skuQty ['SKU-1' => 10, 'SKU-2' => 5]
     * @return \Digidirect\Collect\Api\Data\CollectPlaceInterface[]
     */
    public function getAllCollectPlacesEntities(array $skuQty)
    {
        return $this->collectPlaceRepository->getListBySkus(array_keys($skuQty), $skuQty);
    }
}
