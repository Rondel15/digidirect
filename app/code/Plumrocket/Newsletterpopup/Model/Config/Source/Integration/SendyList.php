<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2018 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Model\Config\Source\Integration;

class SendyList extends \Magento\Framework\App\Config\Value
{
    /**
     * Prepare data before save
     *
     * @return \Magento\Framework\App\Config\Value
     */
    public function beforeSave()
    {
        /** @var array $value */
        $value = $this->getValue();

        unset($value['__empty']);
        $this->setValue(json_encode($value));

        return parent::beforeSave();
    }

    /**
     * Prepare data after load
     *
     * @return \Magento\Framework\App\Config\Value
     */
    protected function _afterLoad()
    {
        /** @var string $value */
        $value = $this->getValue();

        if (! empty($value)) {
            $this->setValue(json_decode($value, true));
        }

        return parent::_afterLoad();
    }
}
