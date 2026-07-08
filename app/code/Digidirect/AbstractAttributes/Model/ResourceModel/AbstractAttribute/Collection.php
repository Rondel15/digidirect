<?php
namespace Digidirect\AbstractAttributes\Model\ResourceModel\AbstractAttribute;

use Digidirect\AbstractAttributes\Model\ResourceModel\AbstractCollection;
use Digidirect\AbstractAttributes\Api\Data\AbstractAttributeInterface;
use Digidirect\AbstractAttributes\Model\AbstractAttribute;
use Digidirect\AbstractAttributes\Model\ResourceModel\AbstractAttribute as AbstractAttributeResource;

/**
 * Class Collection
 * @package Digidirect\AbstractAttributes\Model\ResourceModel\AbstractAttribute
 */
class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = AbstractAttributeInterface::ID;

    /**
     * @var string
     */
    protected $_idEntityKey = AbstractAttributeInterface::ATTRIBUTE_ID;

    /**
     * Add attribute filter
     * @param int $attrId
     * @return $this
     */
    public function addAttributeFilter($attrId)
    {
        return $this->addFilter(AbstractAttributeInterface::ATTRIBUTE_ID, $attrId);
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(
            AbstractAttribute::class,
            AbstractAttributeResource::class
        );
        $this->addFilterToMap('attribute_id', 'main_table.attribute_id');
    }

    /**
     * {@inheritdoc}
     */
    protected function _initSelect()
    {
        parent::_initSelect();

        $this->getSelect()->join(
            ['eav' => $this->getTable('eav_attribute')],
            'eav.attribute_id = main_table.attribute_id',
            AbstractAttributeInterface::EAV_FIELDS
        );

        return $this;
    }
}
