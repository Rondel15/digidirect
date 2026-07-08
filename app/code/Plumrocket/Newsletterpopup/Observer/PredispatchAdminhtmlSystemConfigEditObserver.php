<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2018 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Observer;

use Magento\Framework\Event\ObserverInterface;

class PredispatchAdminhtmlSystemConfigEditObserver implements ObserverInterface
{
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;

    /**
     * @var \Plumrocket\Newsletterpopup\Helper\Config
     */
    private $config;

    /**
     * @var \Plumrocket\Newsletterpopup\Model\Integration\ActiveCampaign
     */
    private $activeCampaign;

    /**
     * @param \Magento\Framework\Message\ManagerInterface                  $messageManager
     * @param \Plumrocket\Newsletterpopup\Helper\Config                    $config
     * @param \Plumrocket\Newsletterpopup\Model\Integration\ActiveCampaign $activeCampaign
     */
    public function __construct(
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Plumrocket\Newsletterpopup\Helper\Config $config,
        \Plumrocket\Newsletterpopup\Model\Integration\ActiveCampaign $activeCampaign
    ) {
        $this->config = $config;
        $this->messageManager = $messageManager;
        $this->activeCampaign = $activeCampaign;
    }

    /**
     * Add warning.
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->config->isModuleEnabled()
            && $this->activeCampaign->isEnable()
            && empty($this->activeCampaign->getAllLists())
        ) {
            $this->messageManager->addWarningMessage(
                __('Active Campaign newsletter subscription will not work until you create at least one Contact List.')
            );
        }
    }
}
