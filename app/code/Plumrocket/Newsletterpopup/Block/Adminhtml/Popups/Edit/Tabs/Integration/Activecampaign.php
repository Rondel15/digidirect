<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2018 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Block\Adminhtml\Popups\Edit\Tabs\Integration;

/**
 * Class Activecampaign
 */
class Activecampaign extends \Plumrocket\Newsletterpopup\Block\Adminhtml\Popups\Edit\Tabs\AbstractIntegration
{
    /**
     * @return string
     */
    public function getIntegrationId()
    {
        return \Plumrocket\Newsletterpopup\Model\Integration\ActiveCampaign::INTEGRATION_ID;
    }

    /**
     * @return string
     */
    public function getIntegrationTitle()
    {
        return __('ActiveCampaign');
    }

    /**
     * @param \Magento\Framework\Data\Form $form
     * @return \Magento\Framework\Data\Form\Element\Fieldset
     */
    protected function addFieldset(\Magento\Framework\Data\Form $form)
    {
        if (! empty($this->getAllLists())) {
            return parent::addFieldset($form);
        }

        $fieldset = $form->addFieldset(
            $this->getIntegrationId() . '_fieldset',
            [
                'legend' => $this->getIntegrationTitle()
            ]
        );

        $this->addFieldNotice($fieldset, $this->getNoticeText(), true);

        return $fieldset;
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getNoticeText()
    {
        $emptyListsNoticeText = __(
            'ActiveCampaign newsletter subscription will not work until you create at least one Contact List '
            . 'at activecampaign.com.'
        );

        return $this->isEnabled() && empty($this->getAllLists())
            ? $emptyListsNoticeText
            : parent::getNoticeText();
    }
}
