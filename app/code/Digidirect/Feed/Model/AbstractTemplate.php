<?php

namespace Digidirect\Feed\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Serialize;

/**
 * Abstract Template Model
 *
 * @method string getType()
 * @method $this setType($type)
 *
 * @method $this setFormat($format)
 *
 * @method array getCsvSchema()
 * @method $this setCsvSchema(array $schema)
 *
 * @method string getXmlSchema()
 * @method $this setXmlSchema($schema)
 */
abstract class AbstractTemplate extends AbstractModel
{
    /**
     * @var Serialize
     */
    protected $serializer;

    /**
     * AbstractTemplate constructor.
     * @param Context $context
     * @param Registry $registry
     * @param Serialize $serializer
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Serialize $serializer,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->serializer = $serializer;

        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * {@inheritdoc}
     */
    protected function _afterLoad()
    {
        $this->extract();
        return parent::_afterLoad();
    }

    /**
     * {@inheritdoc}
     */
    public function beforeSave()
    {
        $this->serializeFormat();
        return parent::beforeSave();
    }

    /**
     * Serialize csv/xml data to format_serialized
     *
     * @return $this
     */
    protected function serializeFormat()
    {
        if ($this->isCsv()) {
            if ($this->hasData('csv')) {
                $this->setData('format_serialized', $this->serializer->serialize($this->getData('csv')));
            }
        } else {
            if ($this->hasData('xml')) {
                $this->setData('format_serialized', $this->serializer->serialize($this->getData('xml')));
            }
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @return bool
     */
    public function isCsv()
    {
        return in_array($this->getType(), ['txt', 'csv']);
    }

    /**
     * Is Xml?
     *
     * @return bool
     */
    public function isXml()
    {
        return !$this->isCsv();
    }

    /**
     * Is object instance of feed?
     *
     * @return bool
     */
    public function isFeed()
    {
        return $this instanceof Feed;
    }

    /**
     * Is object instance of template?
     *
     * @return bool
     */
    public function isTemplate()
    {
        return $this instanceof Template;
    }

    /**
     * Extract csv/xml values from format_serialized
     *
     * @return $this
     */
    protected function extract()
    {
        try {
            $formatSerialized = $this->getData('format_serialized');
            $data = $formatSerialized ? $this->serializer->unserialize($formatSerialized) : [];
        } catch (\Exception $e) {
            $data = [];
        }

        if ($this->isCsv()) {
            foreach ($data as $key => $value) {
                $this->setData('csv_' . $key, $value);
            }

            if (is_array($this->getCsvSchema())) {
                // sort columns by order
                $orders = [];
                $schema = $this->getCsvSchema();
                foreach ($schema as $key => $row) {
                    $orders[$key] = isset($row['order']) ? $row['order'] : 0;
                }
                array_multisort($orders, SORT_ASC, $schema);
                $this->setData('csv_schema', $schema);
            } else {
                $this->setCsvSchema([]);
            }
        } else {
            foreach ($data as $key => $value) {
                $this->setData('xml_' . $key, $value);
            }
        }

        return $this;
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
        $this->serializeFormat()
            ->extract();

        $liquid = '';

        if ($this->isCsv()) {
            $delimiter = $this->getData('csv_delimiter') == 'tab' ? "\t" : $this->getData('csv_delimiter');
            $enclosure = $this->getData('csv_enclosure');

            if ($this->getData('csv_extra_header')) {
                $liquid .= $this->getData('csv_extra_header') . PHP_EOL;
            }

            if ($this->getData('csv_include_header')) {

                $headers = array_map(function ($column) {
                    return $column['header'];
                }, $this->getCsvSchema());

                $liquid .= implode($delimiter, $headers) . PHP_EOL;
            }

            $liquid .= '{% for product in context.products %}' . PHP_EOL;

            $columns = [];
            foreach ($this->getCsvSchema() as $column) {
                $variable = '';
                if ($column['type'] == 'pattern') {
                    $variable .= $column['pattern'];
                } elseif (isset($column['attribute']) && $column['attribute']) {
                    $variable .= '{{ product';

                    if ($column['type']) {
                        $variable .= '.parent';
                    }

                    $variable .= '.' . $column['attribute'];

                    $column['modifiers'][] = [
                        'modifier' => 'csv',
                        'args' => [$delimiter, $enclosure]
                    ];

                    foreach ($column['modifiers'] as $modifier) {
                        if (!$modifier['modifier']) {
                            continue;
                        }

                        $modifier['args'] = isset($modifier['args']) ? $modifier['args'] : [];

                        $variable .= ' | ' . $modifier['modifier'];

                        $args = array_map(function (&$arg) {
                            if (is_string($arg)) {
                                $arg = str_replace("'", "\'", $arg);
                                $arg = "'$arg'";
                            }

                            return $arg;
                        }, $modifier['args']);

                        if ($args) {
                            $variable .= ': ' . implode(', ', $args);
                        }
                    }

                    $variable .= ' }}';
                }

                $columns[] = $variable;
            }

            $liquid .= implode($delimiter, $columns) . PHP_EOL;

            $liquid .= '{% endfor %}';
        } else {
            $liquid = $this->getXmlSchema();
        }

        return $liquid;
    }
}
