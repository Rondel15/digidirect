<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Digidirect\AI\Model\Integrations\RenderSource;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\View\Model\PageLayout\Config\BuilderInterface;

/**
 * Class PageLayout
 */
class ChildProcess implements OptionSourceInterface
{
    /**
     * Options
     *
     * @var array
     */
    protected $options;

    /**
     * ObjectManagerInterface
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * Class constructor
     *
     * @param \Magento\Framework\ObjectManagerInterface $manager
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $manager
    ) {
        $this->_objectManager = $manager;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        if ($this->options !== null) {
            return $this->options;
        }

        $options = [];

        $processes = $this->_objectManager
            ->create('Digidirect\AI\Model\ResourceModel\Integrations\Integrations\Grid\Collection')
            ->getItems();

        if (!count($processes)) {
            $processes = [];
        }

        $options[] = [
            'label' => 'empty',
            'value' => '',
        ];

        foreach ($processes as $process) {
            $options[] = [
                'label' => $process['process_code'],
                'value' => $process['process_code'],
            ];
        }

        $this->options = $options;
        return $this->options;
    }
}
