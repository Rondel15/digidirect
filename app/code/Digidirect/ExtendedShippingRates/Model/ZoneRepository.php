<?php
namespace Digidirect\ExtendedShippingRates\Model;

use Digidirect\ExtendedShippingRates\Api\ZoneRepositoryInterface;
use Digidirect\ExtendedShippingRates\Model\ResourceModel\Zone as ZoneResource;

class ZoneRepository extends AbstractRepository implements ZoneRepositoryInterface
{
    /**
     * ZoneRepository constructor.
     * @param ZoneResource $resource
     * @param ZoneFactory $factory
     */
    public function __construct(
        ZoneResource $resource,
        ZoneFactory $factory
    ) {
        parent::__construct($resource, $factory);
    }
}
