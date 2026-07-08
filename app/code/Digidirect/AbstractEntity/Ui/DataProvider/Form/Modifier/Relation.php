<?php
namespace Digidirect\AbstractEntity\Ui\DataProvider\Form\Modifier;

use Magento\Ui\Component\Form;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;
use Digidirect\AbstractEntity\Model\ResourceModel\Relation as RelationModel;

/**
 * Class Relation
 * @package Digidirect\AbstractEntity\Ui\DataProvider\Form\Modifier
 */
class Relation extends AbstractModifier
{
    const GROUP_RELATION = 'relation';
    const DATA_SCOPE = 'relation_listing';
    const SORT_ORDER = 100;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var RelationModel
     */
    protected $_relation;

    /**
     * Relation constructor.
     * @param Registry $registry
     * @param RequestInterface $request
     * @param UrlInterface $urlBuilder
     * @param RelationModel $relation
     */
    public function __construct(
        Registry $registry,
        RequestInterface $request,
        UrlInterface $urlBuilder,
        RelationModel $relation
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->_relation = $relation;
        parent::__construct($registry, $request);
    }

    /**
     * @param array $meta
     * @return array
     */
    public function modifyMeta(array $meta)
    {
        if (!$this->getCurrentEntity()->getEntityId()) {
            return $meta;
        }

        $setId = $this->getCurrentEntity()->getAttributeSetId();
        if ($this->_relation->getAttributeSetIdsByParent($setId)) {
            $meta[self::GROUP_RELATION] = [
                'children' => [
                    self::DATA_SCOPE => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'autoRender' => true,
                                    'componentType' => 'insertListing',
                                    'dataScope' => self::DATA_SCOPE,
                                    'externalProvider' => 'relation_listing.relation_listing_data_source',
                                    'ns' => self::DATA_SCOPE,
                                    'render_url' => $this->urlBuilder->getUrl('mui/index/render'),
                                    'realTimeLink' => false,
                                    'behaviourType' => 'simple',
                                    'externalFilterMode' => true,
                                    'imports' => [
                                        'entityId' => '${ $.provider }:data.entity.current_entity_id',
                                        'attributeSetId' =>
                                            '${ $.provider }:data.attribute_set.current_attribute_set_id'
                                    ],
                                    'exports' => [
                                        'entityId' => '${ $.externalProvider }:params.current_entity_id',
                                        'attributeSetId' =>
                                            '${ $.externalProvider }:params.current_attribute_set_id'
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'arguments' => [
                    'data' => [
                        'config' => [
                            'label' => __('Related Entity\'s values'),
                            'collapsible' => true,
                            'opened' => false,
                            'componentType' => Form\Fieldset::NAME,
                            'sortOrder' => self::SORT_ORDER,
                        ],
                    ],
                ],
            ];
        }

        return $meta;
    }

    /**
     * @param array $data
     * @return array
     */
    public function modifyData(array $data)
    {
        $entityId = $this->getCurrentEntity()->getEntityId();
        $setId = $this->getCurrentEntity()->getAttributeSetId();

        $data[$entityId]['entity']['current_entity_id'] = $entityId;
        $data[$entityId]['attribute_set']['current_attribute_set_id'] = $setId;

        return $data;
    }
}
