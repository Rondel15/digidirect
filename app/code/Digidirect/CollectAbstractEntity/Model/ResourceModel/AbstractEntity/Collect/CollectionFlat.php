<?php
namespace Digidirect\CollectAbstractEntity\Model\ResourceModel\AbstractEntity\Collect;

use Digidirect\AbstractEntity\Model\ResourceModel\AbstractEntity\FlatCollection as AbstractEntityFlatCollection;
use Digidirect\AbstractEntity\Model\ResourceModel\AbstractEntityFlat as AbstractEntityFlatResource;
use Digidirect\CollectAbstractEntity\Model\AbstractEntityCollectPlace;

/**
 * Class CollectionFlat
 * @package Digidirect\CollectAbstractEntity\Model\ResourceModel\AbstractEntity\Collect
 */
class CollectionFlat extends AbstractEntityFlatCollection
{
    public function _construct()
    {
        $this->_init(AbstractEntityCollectPlace::class, AbstractEntityFlatResource::class);
    }
}
