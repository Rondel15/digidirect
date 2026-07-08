<?php

namespace Digidirect\Feed\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;
use Digidirect\Feed\Model\Config;
use Digidirect\Feed\Model\ResourceModel\Rule\CollectionFactory as RuleCollectionFactory;

class Rule implements ArrayInterface
{
    /**
     * @var RuleCollectionFactory
     */
    protected $ruleCollectionFactory;

    /**
     * @var Config
     */
    protected $config;

    /**
     * Constructor
     *
     * @param RuleCollectionFactory $ruleCollectionFactory
     * @param Config $config
     */
    public function __construct(
        RuleCollectionFactory $ruleCollectionFactory,
        Config $config
    ) {
        $this->ruleCollectionFactory = $ruleCollectionFactory;
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray($filesystem = false)
    {
        $result = [];

        if ($filesystem) {
            $path = $this->config->getRulePath();
            if ($handle = opendir($path)) {
                while (false !== ($entry = readdir($handle))) {
                    if (substr($entry, 0, 1) != '.') {
                        $result[] = [
                            'label' => $entry,
                            'value' => $path . '/' . $entry,
                        ];
                    }
                }
                closedir($handle);
            }
        } else {
            $result = $this->ruleCollectionFactory->create()->toOptionArray();
        }

        sort($result);

        return $result;
    }
}
