<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2018 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Model\Config\Source\Integration;

/**
 * Class IntegrationList
 */
abstract class AbstractIntegrationList implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return null|\Plumrocket\Newsletterpopup\Model\AbstractIntegration
     */
    abstract public function getModel();

    /**
     * @return array
     */
    public function toOptionHash()
    {
        $model = $this->getModel();

        return $model instanceof \Plumrocket\Newsletterpopup\Model\AbstractIntegration
            ? $model->getAllLists() : [];
    }

    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        $values = $this->toOptionHash();
        $result = [];

        foreach ($values as $key => $value) {
            $result[] = [
                'value'    => $key,
                'label'    => $value,
            ];
        }

        return $result;
    }
}
