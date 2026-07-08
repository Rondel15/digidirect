<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Model;

use Magento\Framework\Model\AbstractModel;
use Plumrocket\Newsletterpopup\Api\Data\PopupFieldDataInterface;

class FormField extends AbstractModel implements PopupFieldDataInterface
{
    protected function _construct()
    {
        $this->_init(ResourceModel\FormField::class);
    }

    public function isEnabled(): bool
    {
        return (bool) $this->getEnabled();
    }

    public function getPopupId(): int
    {
        return (int) $this->getData(self::POPUP_ID);
    }

    public function getName(): string
    {
        return (string) $this->getData(self::NAME);
    }

    public function getLabel(): string
    {
        return (string) $this->getData(self::LABEL);
    }

    public function getEnabled(): int
    {
        return (int) $this->getData(self::ENABLED);
    }

    public function getSortOrder(): int
    {
        return (int) $this->getData(self::SORT_ORDER);
    }

    public function setPopupId($popupId): PopupFieldDataInterface
    {
        return $this->setData(self::POPUP_ID, (int) $popupId);
    }

    public function setName($name): PopupFieldDataInterface
    {
        return $this->setData(self::NAME, (string) $name);
    }

    public function setLabel($label): PopupFieldDataInterface
    {
        return $this->setData(self::LABEL, (string) $label);
    }

    public function setEnabled($enabled): PopupFieldDataInterface
    {
        return $this->setData(self::ENABLED, (int) $enabled);
    }

    public function setSortOrder($sortOrder): PopupFieldDataInterface
    {
        return $this->setData(self::SORT_ORDER, (int) $sortOrder);
    }
}
