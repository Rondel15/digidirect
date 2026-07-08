<?php

namespace Digidirect\AbstractEntity\Ui\DataProvider\Form\Modifier;

use Digidirect\AbstractEntity\Api\AbstractEntityRepositoryInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Digidirect\AbstractEntity\Model\ResourceModel\AbstractEntity\CollectionFactory as AbstractEntityCollectionFactory;
use Digidirect\AbstractEntity\Model\ResourceModel\Relation as RelationResourceModel;
use Digidirect\AbstractEntity\Api\Data\AbstractEntityInterface;
use Magento\Framework\App\ObjectManager;

/**
 * Class Additional
 * @package Digidirect\AbstractEntity\Ui\DataProvider\Form\Modifier
 */
class Additional extends AbstractModifier
{
    const DEFAULT_FIELDSET = 'general';
    const PARENT_NAME_PARAM = 'parent_name';

    /**
     * @var array
     */
    protected $_options;

    /**
     * @var AbstractEntityCollectionFactory
     */
    protected $_abstractEntityCollectionFactory;

    /**
     * @var RelationResourceModel
     */
    protected $_relation;

    /**
     * @var AbstractEntityRepositoryInterface
     */
    protected $abstractEntityRepository;

    /**
     * Additional constructor.
     * @param Registry                               $registry
     * @param RequestInterface                       $request
     * @param AbstractEntityCollectionFactory        $abstractEntityCollectionFactory
     * @param RelationResourceModel                  $relationInstance
     * @param AbstractEntityRepositoryInterface|null $abstractEntityRepository
     */
    public function __construct(
        Registry $registry,
        RequestInterface $request,
        AbstractEntityCollectionFactory $abstractEntityCollectionFactory,
        RelationResourceModel $relationInstance,
        AbstractEntityRepositoryInterface $abstractEntityRepository = null
    ) {
        $this->abstractEntityRepository = $abstractEntityRepository ?: ObjectManager::getInstance()->get(
            AbstractEntityRepositoryInterface::class
        );

        $this->_abstractEntityCollectionFactory = $abstractEntityCollectionFactory;
        $this->_relation = $relationInstance;
        parent::__construct($registry, $request);
    }

    /**
     * @param array $data
     * @return array
     */
    public function modifyData(array $data)
    {
        try {
            $data = $this->addAdditionData($data);
        } catch (LocalizedException $exception) {
            //TODO Probably need to add some notification
        }

        return $data;
    }

    /**
     * @param array $meta
     * @return array
     */
    public function modifyMeta(array $meta)
    {
        if ($this->getParentAttributeSetId()) {
            $validation = [];
            $isParentRequired = $this->_relation->getIsParentRequired($this->currentAttributeSet());
            if ($isParentRequired) {
                $validation['required-entry'] = true;
            }
            $meta[self::DEFAULT_FIELDSET]['children'][AbstractEntityInterface::PARENT_ID] = [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'required' => $isParentRequired,
                            'config' => [
                                'dataScope' => AbstractEntityInterface::PARENT_ID,
                            ],
                            'validation' => $validation
                        ],
                    ],
                ]
            ];
        } else {
            $meta[self::DEFAULT_FIELDSET]['children'][AbstractEntityInterface::PARENT_ID] = [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'visible' => false,
                        ],
                    ],
                ]
            ];
        }

        return $meta;
    }

    /**
     * @return mixed
     */
    public function currentAttributeSet()
    {
        return $this->request->getParam(AbstractEntityInterface::ATTRIBUTE_SET_ID);
    }

    /**
     * @return null|int
     */
    public function getParentAttributeSetId()
    {
        return $this->_relation->getParentAttributeSetId($this->currentAttributeSet());
    }

    /**
     * @param array $data
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function addAdditionData(array $data)
    {
        if ($abstractEntityId = $this->getParentAttributeSetId()) {
            foreach ($data as $id => $entity) {
                foreach ($entity as $attributeCode => $value) {
                    if ($attributeCode === AbstractEntityInterface::PARENT_ID) {
                        $parentId = (int)$value;
                        $abstractEntity = $this->abstractEntityRepository->getById($parentId);
                        $data[$id][self::PARENT_NAME_PARAM] = $abstractEntity->getName();
                        break 2;
                    }
                }
            }
        }

        return $data;
    }

    /**
     * @return null|array
     * @deprecated
     */
    public function toOptionArray()
    {
        if ($this->_options === null) {
            $this->_options = [['value' => '', 'label' => ' ',]];
            foreach ($this->_getCollection() as $entity) {
                $this->_options[] = [
                    'value' => $entity->getEntityId(),
                    'label' => $entity->getName(),
                ];
            }
        }
        return $this->_options;
    }

    /**
     * @return \Digidirect\AbstractEntity\Model\ResourceModel\AbstractEntity\Collection
     */
    protected function _getCollection()
    {
        $collection = $this->_abstractEntityCollectionFactory->create();

        $collection
            ->addFieldToFilter(AbstractEntityInterface::ATTRIBUTE_SET_ID, $this->getParentAttributeSetId())
            ->addFieldToSelect(AbstractEntityInterface::NAME);
        return $collection;
    }
}
