<?php
namespace Digidirect\CollectAbstractEntity\Model\ResourceModel\AbstractEntity\Collect;

use Digidirect\AbstractEntity\Model\ResourceModel\AbstractEntity\Collection as AbstractEntityCollection;
use Digidirect\AbstractEntity\Model\ResourceModel\AbstractEntity as AbstractEntityResource;
use Digidirect\CollectAbstractEntity\Model\AbstractEntityCollectPlace;

/**
 * Class Collection
 * @package Digidirect\CollectAbstractEntity\Model\ResourceModel\AbstractEntity\Collect
 */
class Collection extends AbstractEntityCollection
{
    public function _construct()
    {
        $this->_init(AbstractEntityCollectPlace::class, AbstractEntityResource::class);
    }
}
