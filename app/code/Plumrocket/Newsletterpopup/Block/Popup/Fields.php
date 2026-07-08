<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Block\Popup;

use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Block\Widget\AbstractWidget;
use Magento\Customer\Helper\Address;
use Magento\Framework\View\Element\Template\Context;
use Plumrocket\Newsletterpopup\Api\Data\PopupFieldDataInterface;
use Plumrocket\Newsletterpopup\Block\Popup\Fields\CountryId;
use Plumrocket\Newsletterpopup\Block\Popup\Fields\DataPrivacyConsents;
use Plumrocket\Newsletterpopup\Block\Popup\Fields\Field;
use Plumrocket\Newsletterpopup\Model\Popup\GetFields as GetPopupFields;

class Fields extends AbstractWidget
{
    /**
     * @var GetPopupFields
     */
    private $getPopupFields;

    /**
     * @var string[]
     */
    private $blockMapping = [
        'data_privacy_consents' => DataPrivacyConsents::class,
        'country_id' => CountryId::class,
    ];

    /**
     * @param \Magento\Framework\View\Element\Template\Context  $context
     * @param \Magento\Customer\Helper\Address                  $addressHelper
     * @param \Magento\Customer\Api\CustomerMetadataInterface   $customerMetadata
     * @param \Plumrocket\Newsletterpopup\Model\Popup\GetFields $getPopupFields
     * @param array                                             $blockMapping
     * @param array                                             $data
     */
    public function __construct(
        Context $context,
        Address $addressHelper,
        CustomerMetadataInterface $customerMetadata,
        GetPopupFields $getPopupFields,
        array $blockMapping = [],
        array $data = []
    ) {
        $this->setTemplate('popup/fields.phtml');

        parent::__construct($context, $addressHelper, $customerMetadata, $data);
        $this->getPopupFields = $getPopupFields;
        $this->blockMapping = array_merge($this->blockMapping, $blockMapping);
    }

    /**
     * @return \Plumrocket\Newsletterpopup\Api\Data\PopupFieldDataInterface[]
     */
    public function getFields(): array
    {
        if ($data = $this->_getPopup()->getData('custom_signup_fields')) {
            return $data;
        }
        $popupId = (int) $this->_getPopup()->getId();
        if ($this->_getPopup()->getIsTemplate()) {
            $popupId = 0;
        }
        return $this->getPopupFields->execute($popupId);
    }

    /**
     * Create block for popup field.
     *
     * @param \Plumrocket\Newsletterpopup\Api\Data\PopupFieldDataInterface $field
     * @return Field|\Magento\Customer\Block\Widget\AbstractWidget
     */
    public function createBlock(PopupFieldDataInterface $field)
    {
        $blockName = 'field';
        if (in_array($field->getName(), ['dob', 'gender', 'prefix', 'suffix', 'agreement', 'recaptcha'])) {
            $blockName = $field->getName();
        }

        if (isset($this->blockMapping[$field->getName()])) {
            $fullBlockName = $this->blockMapping[$field->getName()];
        } else {
            $fullBlockName = 'Plumrocket\Newsletterpopup\Block\Popup\Fields\\' . ucfirst($blockName);
        }

        /** @var Field|AbstractWidget $fieldBlock */
        $fieldBlock = $this->getLayout()->createBlock($fullBlockName);

        return $fieldBlock->setField($field)
            ->setPopup($this->_getPopup());
    }

    /**
     * @param \Plumrocket\Newsletterpopup\Api\Data\PopupFieldDataInterface $field
     * @return string
     */
    public function getFieldHtml($field)
    {
        $fieldBlock = $this->createBlock($field);
        return $fieldBlock ? $fieldBlock->toHtml() : '';
    }

    protected function _getPopup()
    {
        return $this->getParentBlock()->getPopup();
    }
}
