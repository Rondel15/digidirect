<?php

namespace Digidirect\Feed\Model;

use Magento\Rule\Model\AbstractModel;
use Symfony\Component\Yaml\Dumper as YamlDumper;
use Symfony\Component\Yaml\Parser as YamlParser;
use Digidirect\Feed\Api\Data\RuleInterface;
use Magento\Framework\Serialize\Serializer\Serialize;

/**
 * @method ResourceModel\Rule getResource()
 * @SuppressWarnings(PHPMD)
 * @codingStandardsIgnoreFile
 */
class Rule extends AbstractModel implements RuleInterface
{
    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'digidirect_feed_rule';

    /**
     * Parameter name in event
     *
     * In observe method you can use $observer->getEvent()->getObject() in this case
     *
     * @var string
     */
    protected $_eventObject = 'rule';

    /**
     * @var array
     */
    protected $productIds;

    /**
     * @var \Digidirect\Feed\Model\Rule\Condition\CombineFactory
     */
    protected $ruleConditionCombineFactory;

    /**
     * @var \Digidirect\Feed\Model\Rule\Action\CollectionFactory
     */
    protected $ruleActionCollectionFactory;

    /**
     * @var \Digidirect\Feed\Model\RuleFactory
     */
    protected $ruleFactory;

    /**
     * @var \Digidirect\Feed\Model\RuleRepository
     */
    protected $ruleRepository;

    /**
     * @var \Digidirect\Feed\Model\Config
     */
    protected $config;

    /**
     * @var \Magento\Framework\Data\Collection\AbstractDb
     */
    protected $resourceCollection;

    /**
     * @var Serialize
     */
    protected $serializer;

    /**
     * Rule constructor.
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param Rule\Condition\CombineFactory $conditionCombineFactory
     * @param Rule\Action\CollectionFactory $ruleActionCollectionFactory
     * @param RuleFactory $ruleFactory
     * @param RuleRepository $ruleRepository
     * @param Config $config
     * @param Serialize $serializer
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        Rule\Condition\CombineFactory $conditionCombineFactory,
        Rule\Action\CollectionFactory $ruleActionCollectionFactory,
        RuleFactory $ruleFactory,
        RuleRepository $ruleRepository,
        Config $config,
        Serialize $serializer,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $localeDate, $resource, $resourceCollection, $data);
        $this->ruleConditionCombineFactory = $conditionCombineFactory;
        $this->ruleActionCollectionFactory = $ruleActionCollectionFactory;
        $this->ruleFactory = $ruleFactory;
        $this->ruleRepository = $ruleRepository;
        $this->config = $config;
        //parent classes serializer is rewritten here.
        $this->serializer = $serializer;
    }

    protected function _construct()
    {
        parent::_construct();
        $this->_init('Digidirect\Feed\Model\ResourceModel\Rule');
        $this->setIdFieldName('rule_id');
    }

    /**
     * Assigned feed ids
     *
     * @return array
     */
    public function getFeedIds()
    {
        if (!$this->hasData('feed_ids')) {
            $this->setData('feed_ids', $this->getResource()->getFeedIds($this));
        }
        return $this->_getData('feed_ids');
    }

    /**
     * {@inheritdoc}
     * @return Rule\Condition\Combine
     */
    public function getConditionsInstance()
    {
        return $this->ruleConditionCombineFactory->create();
    }

    /**
     * {@inheritdoc}
     * @return Rule\Action\Collection
     */
    public function getActionsInstance()
    {
        return $this->ruleActionCollectionFactory->create();
    }

    /**
     * @return $this
     */
    public function clearProductIds()
    {
        $this->getResource()->clearProductIds($this->getId());

        return $this;
    }

    /**
     * @param array $productIds
     * @return $this
     */
    public function saveProductIds(array $productIds = [])
    {
        $this->getResource()->saveProductIds($this->getId(), $productIds);

        return $this;
    }

    /**
     * Retrieve rule combine conditions model
     *
     * @return  \Magento\Rule\Model\Condition\Combine
     */
    public function getConditions()
    {
        if (empty($this->_conditions)) {
            $this->_resetConditions();
        }

        // Load rule conditions if it is applicable
        if ($this->hasConditionsSerialized()) {
            $conditions = $this->getConditionsSerialized();
            if (!empty($conditions)) {
                $conditions = $this->serializer->unserialize($conditions);
                if (is_array($conditions) && !empty($conditions)) {
                    $this->_conditions->loadArray($conditions);
                }
            }
            $this->unsConditionsSerialized();
        }

        return $this->_conditions;
    }

    /**
     * @param string $format
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function toString($format = '')
    {
        $rule = $this->ruleRepository->getById($this->getId());
        $string = $rule->getConditions()->asStringRecursive();

        $string = nl2br(preg_replace('/ /', '&nbsp;', $string));

        return $string;
    }

    /**
     * @return string
     */
    public function export()
    {
        $path = $this->config->getRulePath() . '/' . $this->getName() . '.yaml';

        $dumper = new YamlDumper();

        $yaml = $dumper->dump($this->toArray([
            'name',
            'conditions_serialized',
            'actions_serialized'
        ]), 10);

        file_put_contents($path, $yaml);

        return $path;
    }

    /**
     * @todo need create typical interface
     */
    public function import($filePath)
    {
        $parser = new YamlParser();

        $content = file_get_contents($filePath);

        $data = $parser->parse($content);

        $model = $this->getCollection()
            ->addFieldToFilter('name', $data['name'])
            ->getFirstItem();

        $model->addData($data)
            ->setIsImport(true)
            ->setIsActive(1)
            ->save();

        return $model;
    }

    public function beforeSave()
    {
        if ($this->getIsImport()) {
            return $this;
        }

        parent::beforeSave();

        // Serialize conditions
        if ($this->getConditions()) {
            $this->setConditionsSerialized($this->serializer->serialize($this->getConditions()->asArray()));
            $this->_conditions = null;
        }

        // Serialize actions
        if ($this->getActions()) {
            $this->setActionsSerialized($this->serializer->serialize($this->getActions()->asArray()));
            $this->_actions = null;
        }

        return $this;
    }

    public function duplicate()
    {
        $this->ruleFactory->create()
            ->addData($this->getData())
            ->setRuleId(null)
            ->setName($this->getName() . ' (copy)')
            ->setCreatedAt(null)
            ->setUpdatedAt(null)
            ->setFeedIds(null)
            ->save();

        return $this;
    }

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId()
    {
        return $this->getData(self::RULE_ID);
    }

    /**
     * Set ID
     *
     * @param int $id
     * @return \Digidirect\Feed\Api\Data\RuleInterface
     */
    public function setId($id)
    {
        return $this->setData(self::RULE_ID, $id);
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->getData(self::NAME);
    }

    /**
     * Set name
     *
     * @param string $name
     * @return \Digidirect\Feed\Api\Data\RuleInterface
     */
    public function setName($name)
    {
        return $this->setData(self::NAME, $name);
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->getData(self::TYPE);
    }

    /**
     * Set type
     *
     * @param string $type
     * @return \Digidirect\Feed\Api\Data\RuleInterface
     */
    public function setType($type)
    {
        return $this->setData(self::TYPE, $type);
    }

    /**
     * Get conditions serialized
     *
     * @return string
     */
    public function getConditionsSerialized()
    {
        return $this->getData(self::CONDITIONS_SERIALIZED);
    }

    /**
     * Set conditions serialized
     *
     * @param string $conditionsSerialized
     * @return \Digidirect\Feed\Api\Data\RuleInterface
     */
    public function setConditionsSerialized($conditionsSerialized)
    {
        return $this->setData(self::CONDITIONS_SERIALIZED, $conditionsSerialized);
    }

    /**
     * Get actions serialized
     *
     * @return string
     */
    public function getActionsSerialized()
    {
        return $this->getData(self::ACTIONS_SERIALIZED);
    }

    /**
     * Set actions serialized
     *
     * @param string $actionsSerialized
     * @return \Digidirect\Feed\Api\Data\RuleInterface
     */
    public function setActionsSerialized($actionsSerialized)
    {
        return $this->setData(self::ACTIONS_SERIALIZED, $actionsSerialized);
    }

    /**
     * Get created at
     *
     * @return string
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * Set created at
     *
     * @param string $createdAt
     * @return \Digidirect\Feed\Api\Data\RuleInterface
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * Get updated at
     *
     * @return string
     */
    public function getUpdatedAt()
    {
        return $this->getData(self::UPDATED_AT);
    }

    /**
     * Set updated at
     *
     * @param string $updatedAt
     * @return \Digidirect\Feed\Api\Data\RuleInterface
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }
}
