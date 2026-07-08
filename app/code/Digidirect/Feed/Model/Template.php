<?php

namespace Digidirect\Feed\Model;

use Digidirect\Feed\Api\Data\TemplateInterface;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Serialize;
use Digidirect\Feed\Model\ResourceModel\Template\CollectionFactory as TemplateCollectionFactory;
use Symfony\Component\Yaml\Dumper as YamlDumper;
use Symfony\Component\Yaml\Parser as YamlParser;

/**
 * Template Model
 *
 * @method bool hasCreatedAt()
 */
class Template extends AbstractTemplate implements TemplateInterface
{
    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'digidirect_feed_template';

    /**
     * Parameter name in event
     *
     * In observe method you can use $observer->getEvent()->getObject() in this case
     *
     * @var string
     */
    protected $_eventObject = 'template';

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var TemplateCollectionFactory
     */
    protected $collectionFactory;

    /**
     * Template constructor.
     * @param Context $context
     * @param Registry $registry
     * @param Serialize $serializer
     * @param Config $config
     * @param TemplateCollectionFactory $templateCollectionFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Serialize $serializer,
        Config $config,
        TemplateCollectionFactory $templateCollectionFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->config = $config;
        $this->collectionFactory = $templateCollectionFactory;

        parent::__construct($context, $registry, $serializer, $resource, $resourceCollection, $data);
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('Digidirect\Feed\Model\ResourceModel\Template');
    }

    /**
     * Export template to file
     *
     * @return string
     */
    public function export()
    {
        $path = $this->config->getTemplatePath() . '/' . $this->getName() . '.yaml';

        $dumper = new YamlDumper();

        $yaml = $dumper->dump($this->toArray([
            'name',
            'type',
            'xml_schema',
            'csv_delimiter',
            'csv_enclosure',
            'csv_include_header',
            'csv_extra_header',
            'csv_schema'
        ]), 10);

        file_put_contents($path, $yaml);

        return $path;
    }

    /**
     * Import template from file
     *
     * @param string $filePath
     * @return $this
     */
    public function import($filePath)
    {
        $parser = new YamlParser();

        $content = file_get_contents($filePath);

        $data = $parser->parse($content);

        $model = $this->collectionFactory->create()
            ->addFieldToFilter('name', $data['name'])
            ->getFirstItem();

        $model->addData($data)
            ->save();

        return $model;
    }

    /**
     * Return liquid template for csv/xml
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     *  @SuppressWarnings(PHPMD.NPathComplexity) 
     *
     * @return string
     */
    public function getLiquidTemplate()
    {
        $result = parent::getLiquidTemplate();

        if (empty($result) && $this->isXml()) {
            $result = '<!-- Xml Feed -->';
        }

        return $result;
    }

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId()
    {
        return $this->getData(self::TEMPLATE_ID);
    }

    /**
     * Set ID
     *
     * @param int $id
     * @return \Digidirect\Feed\Api\Data\TemplateInterface
     */
    public function setId($id)
    {
        return $this->setData(self::TEMPLATE_ID, $id);
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
     * @return \Digidirect\Feed\Api\Data\TemplateInterface
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
     * @return \Digidirect\Feed\Api\Data\TemplateInterface
     */
    public function setType($type)
    {
        return $this->setData(self::TYPE, $type);
    }

    /**
     * Get format serialized
     *
     * @return string
     */
    public function getFormatSerialized()
    {
        return $this->getData(self::FORMAT_SERIALIZED);
    }

    /**
     * Set format serialized
     *
     * @param string $formatSerialized
     * @return \Digidirect\Feed\Api\Data\TemplateInterface
     */
    public function setFormatSerialized($formatSerialized)
    {
        return $this->setData(self::FORMAT_SERIALIZED, $formatSerialized);
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
     * @return \Digidirect\Feed\Api\Data\TemplateInterface
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
     * @return \Digidirect\Feed\Api\Data\TemplateInterface
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }
}
