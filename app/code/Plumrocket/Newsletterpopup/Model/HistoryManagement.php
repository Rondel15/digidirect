<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2023 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\Newsletterpopup\Model;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\StateException;
use Plumrocket\Newsletterpopup\Api\Data\HistoryInterface;
use Plumrocket\Newsletterpopup\Api\Data\PopupInterface;
use Plumrocket\Newsletterpopup\Api\HistoryManagementInterface;
use Plumrocket\Newsletterpopup\Helper\Config;
use Plumrocket\Newsletterpopup\Model\Config\Source\Action;
use Psr\Log\LoggerInterface;

class HistoryManagement implements HistoryManagementInterface
{

    /**
     * @var \Plumrocket\Newsletterpopup\Api\Data\HistoryInterfaceFactory
     */
    private $historyFactory;

    /**
     * @var \Plumrocket\Newsletterpopup\Model\HistoryRepository
     */
    private $historyRepository;

    /**
     * @var \Plumrocket\Newsletterpopup\Helper\Config
     */
    private $config;

    /**
     * @var \Plumrocket\Newsletterpopup\Model\ResourceModel\Popup
     */
    private $popupResource;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @param \Plumrocket\Newsletterpopup\Api\Data\HistoryInterfaceFactory $historyFactory
     * @param \Plumrocket\Newsletterpopup\Model\HistoryRepository          $historyRepository
     * @param \Plumrocket\Newsletterpopup\Helper\Config                    $config
     * @param \Plumrocket\Newsletterpopup\Model\ResourceModel\Popup        $popupResource
     * @param \Psr\Log\LoggerInterface                                     $logger
     */
    public function __construct(
        \Plumrocket\Newsletterpopup\Api\Data\HistoryInterfaceFactory $historyFactory,
        HistoryRepository $historyRepository,
        Config $config,
        \Plumrocket\Newsletterpopup\Model\ResourceModel\Popup $popupResource,
        LoggerInterface $logger
    ) {
        $this->historyFactory = $historyFactory;
        $this->historyRepository = $historyRepository;
        $this->config = $config;
        $this->popupResource = $popupResource;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function logCanceling(PopupInterface $popup): ?HistoryInterface
    {
        $this->popupResource->updateViewsCount($popup);

        if (! $this->config->isHistoryEnabled()) {
            return null;
        }
        /** @var \Plumrocket\Newsletterpopup\Api\Data\HistoryInterface $history */
        $history = $this->historyFactory->create();
        $history->setPopupId((int) $popup->getId());
        $history->setAction(Action::CANCEL);
        try {
            return $this->historyRepository->save($history);
        } catch (CouldNotSaveException|InputException|StateException $e) {
            $this->logger->error($e);
            return null;
        }
    }
}
