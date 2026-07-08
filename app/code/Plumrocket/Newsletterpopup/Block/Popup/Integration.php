<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2018 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Block\Popup;

use Plumrocket\Newsletterpopup\Model\Config\Source\SubscriptionMode;

class Integration extends \Magento\Framework\View\Element\Template
{
    /**
     * @var string
     */
    protected $_template = 'popup/integration.phtml';

    /**
     * @var \Plumrocket\Newsletterpopup\Model\IntegrationRepositoryInterface
     */
    private $integrationRepository;

    /**
     * @var \Plumrocket\Newsletterpopup\Model\ResourceModel\MailchimpList\CollectionFactory
     */
    private $listCollectionFactory;

    /**
     * @var array
     */
    private $allowedModes = [
        SubscriptionMode::ONE_LIST_RADIO,
        SubscriptionMode::ONE_LIST_SELECT,
        SubscriptionMode::MUPTIPLE_LIST,
    ];

    /**
     * @var \Plumrocket\Newsletterpopup\Helper\Config
     */
    private $config;

    /**
     * @param \Magento\Framework\View\Element\Template\Context                                $context
     * @param \Plumrocket\Newsletterpopup\Model\IntegrationRepositoryInterface                $integrationRepository
     * @param \Plumrocket\Newsletterpopup\Model\ResourceModel\MailchimpList\CollectionFactory $listCollectionFactory
     * @param \Plumrocket\Newsletterpopup\Helper\Config                                       $config
     * @param array                                                                           $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Plumrocket\Newsletterpopup\Model\IntegrationRepositoryInterface $integrationRepository,
        \Plumrocket\Newsletterpopup\Model\ResourceModel\MailchimpList\CollectionFactory $listCollectionFactory,
        \Plumrocket\Newsletterpopup\Helper\Config $config,
        array $data = []
    ) {
        $this->integrationRepository = $integrationRepository;
        $this->listCollectionFactory = $listCollectionFactory;
        $this->config = $config;
        parent::__construct($context, $data);
    }

    /**
     * @return null|\Plumrocket\Newsletterpopup\Model\Popup
     */
    public function getPopup()
    {
        return $this->getParentBlock()->getPopup();
    }

    /**
     * @return array
     */
    public function getLists()
    {
        $result = [];
        $popup = $this->getPopup();

        if ($popup
            && ($popup->getId() > 0)
        ) {
            $result = $this->getGroupedSavedLists();

            foreach (array_keys($result) as $integrationId) {
                if ('mailchimp' === $integrationId) {
                    if (! $this->config->isMaichimpEnabled()
                        || ! $popup->getPreparedIntegrationEnable('mailchimp')
                    ) {
                        unset($result[$integrationId]);
                    }

                    continue;
                }

                if (! $this->integrationRepository->get($integrationId)->isEnable()) {
                    unset($result[$integrationId]);
                }
            }
        }

        return $result;
    }

    /**
     * @return array
     */
    public function getPopupIntegrationModes()
    {
        if ($popup = $this->getPopup()) {
            $result = $popup->getPreparedIntegrationMode();
            $enabled = $popup->getPreparedIntegrationEnable();

            if (! is_array($result)) {
                $result = [];
            }

            if ($this->config->isMaichimpEnabled()) {
                $result['mailchimp'] = (string)$popup->getData('subscription_mode');
            }

            foreach ($result as $integrationId => $value) {
                if (empty($enabled[$integrationId])) {
                    unset($result[$integrationId]);
                }
            }

            return $result;
        }

        return [];
    }

    /**
     * @return array
     */
    private function getGroupedSavedLists()
    {
        $result = [];
        $popup = $this->getPopup();

        if ($popup && $popup->getId()) {
            $integrationIds = [];
            $enableData = $popup->getPreparedIntegrationEnable();

            if (is_array($enableData) && ! empty($enableData)) {
                foreach ($enableData as $integrationId => $value) {
                    if (0 !== (int)$value) {
                        $integrationIds[] = $integrationId;
                    }
                }
            }

            if (! empty($integrationIds)) {
                /** @var \Plumrocket\Newsletterpopup\Model\ResourceModel\MailchimpList\Collection $collection */
                $collection = $this->listCollectionFactory->create();
                $collection->addIntegrationAndPopupFilter($integrationIds, $this->getPopup()->getId(), true);
                $result = $collection->getGroupedIntegrationLists();
            }
        }

        return $result;
    }

    /**
     * @return bool
     */
    public function canShow()
    {
        foreach ($this->getPopupIntegrationModes() as $mode) {
            if (in_array($mode, $this->allowedModes, true)) {
                return true;
            }
        }

        return false;
    }
}
