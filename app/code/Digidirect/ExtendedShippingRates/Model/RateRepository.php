<?php
namespace Digidirect\ExtendedShippingRates\Model;

use Digidirect\ExtendedShippingRates\Api\RateRepositoryInterface;
use Digidirect\ExtendedShippingRates\Model\ResourceModel\Rate as RateResource;
use Digidirect\ExtendedShippingRates\Model\Carrier\Method\RateFactory;

class RateRepository extends AbstractRepository implements RateRepositoryInterface
{
    /**
     * RateRepository constructor.
     * @param RateResource $resource
     * @param RateFactory $factory
     */
    public function __construct(
        RateResource $resource,
        RateFactory $factory
    ) {
        parent::__construct($resource, $factory);
    }
}
