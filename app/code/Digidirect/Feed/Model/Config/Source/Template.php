<?php

namespace Digidirect\Feed\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;
use Digidirect\Feed\Model\Config;
use Digidirect\Feed\Model\ResourceModel\Template\CollectionFactory as TemplateCollectionFactory;

class Template implements ArrayInterface
{
    /**
     * @var TemplateCollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var Config
     */
    protected $config;

    /**
     * Constructor
     *
     * @param TemplateCollectionFactory $collectionFactory
     * @param Config $config
     */
    public function __construct(
        TemplateCollectionFactory $collectionFactory,
        Config $config
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray($filesystem = false, $path = null)
    {
        $result = [];

        if ($filesystem) {
            if (!$path) {
                $path = $this->config->getTemplatePath();
            }
            if ($handle = opendir($path)) {
                while (false !== ($entry = readdir($handle))) {
                    if (substr($entry, 0, 1) != '.') {
                        $result[] = [
                            'label' => pathinfo($entry, PATHINFO_FILENAME),
                            'value' => $path . '/' . $entry,
                        ];
                    }
                }
                closedir($handle);
            }
        } else {
            $result[] = [
                'label' => 'Empty Template',
                'value' => ''
            ];

            /** @var \Digidirect\Feed\Model\Template $template */
            foreach ($this->collectionFactory->create() as $template) {
                $result[] = [
                    'label' => $template->getName() . ' (' . $template->getType() . ')',
                    'value' => $template->getId(),
                ];
            }
        }
        sort($result);

        return $result;
    }
}
