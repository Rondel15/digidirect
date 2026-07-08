<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2018 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Block\Adminhtml\System\Config\Form\Integration;

/**
 * Class ActivecampaignEnable
 */
class ActivecampaignEnable extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @var \Plumrocket\Newsletterpopup\Model\Integration\ActiveCampaign
     */
    private $activeCampaign;

    /**
     * @var null|bool
     */
    private $showWarning = null;

    /**
     * ActivecampaignEnable constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Plumrocket\Newsletterpopup\Model\Integration\ActiveCampaign $activeCampaign
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Plumrocket\Newsletterpopup\Model\Integration\ActiveCampaign $activeCampaign,
        array $data = []
    ) {
        $this->activeCampaign = $activeCampaign;
        parent::__construct($context, $data);
    }

    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @param string $html
     * @return string
     */
    protected function _decorateRowHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element, $html)
    {
        $html = parent::_decorateRowHtml($element, $html);

        if ($this->canShowWarning()) {
            $html = $this->getWarningMessage() . $html;
        }

        return $html;
    }

    /**
     * @return string
     */
    public function getWarningMessage()
    {
        return sprintf(
            '<div class="messages"><div class="message message-warning warning"><div>%s</div></div></div>',
            $this->getWarningText()
        );
    }

    /**
     * @return string
     */
    public function getWarningText()
    {
        return __('Active Campaign newsletter subscription will not work until you create at least one Contact List.');
    }

    /**
     * @return bool|null
     */
    public function canShowWarning()
    {
        if (null === $this->showWarning) {
            $this->showWarning = $this->activeCampaign->isEnable() && empty($this->activeCampaign->getAllLists());
        }

        return $this->showWarning;
    }
}
