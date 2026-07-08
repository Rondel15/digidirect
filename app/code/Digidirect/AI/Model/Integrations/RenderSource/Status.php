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
class Status implements OptionSourceInterface
{
    /**
     * Options
     *
     * @var array
     */
    protected $options;

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

        $options[] = [
            'label' => '',
            'value' => '',
        ];

        $options[] = [
            'label' => \Digidirect\AI\Model\Integrations\Integrations::STATUS_PENDING,
            'value' => \Digidirect\AI\Model\Integrations\Integrations::STATUS_PENDING,
        ];

        $options[] = [
            'label' => \Digidirect\AI\Model\Integrations\Integrations::STATUS_DISABLED,
            'value' => \Digidirect\AI\Model\Integrations\Integrations::STATUS_DISABLED,
        ];

        $this->options = $options;

        return $this->options;
    }
}
