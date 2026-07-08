<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\Newsletterpopup\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Plumrocket\Newsletterpopup\Api\HistoryManagementInterface;
use Plumrocket\Newsletterpopup\Api\PopupRepositoryInterface;
use Plumrocket\Newsletterpopup\Helper\Config;
use Plumrocket\Newsletterpopup\Helper\Data;

class Cancel extends Action
{

    /**
     * @var \Plumrocket\Newsletterpopup\Helper\Data
     */
    private $dataHelper;

    /**
     * @var \Plumrocket\Newsletterpopup\Helper\Config
     */
    private $config;

    /**
     * @var \Plumrocket\Newsletterpopup\Api\PopupRepositoryInterface
     */
    private $popupRepository;

    /**
     * @var \Plumrocket\Newsletterpopup\Api\HistoryManagementInterface
     */
    private $historyManagement;

    /**
     * @param \Magento\Framework\App\Action\Context                      $context
     * @param \Plumrocket\Newsletterpopup\Helper\Data                    $dataHelper
     * @param \Plumrocket\Newsletterpopup\Helper\Config                  $config
     * @param \Plumrocket\Newsletterpopup\Api\PopupRepositoryInterface   $popupRepository
     * @param \Plumrocket\Newsletterpopup\Api\HistoryManagementInterface $historyManagement
     */
    public function __construct(
        Context $context,
        Data $dataHelper,
        Config $config,
        PopupRepositoryInterface $popupRepository,
        HistoryManagementInterface $historyManagement
    ) {
        parent::__construct($context);
        $this->dataHelper = $dataHelper;
        $this->config = $config;
        $this->popupRepository = $popupRepository;
        $this->historyManagement = $historyManagement;
    }

    /**
     * Cancel subscription.
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute(): Json
    {
        if (! $this->config->isModuleEnabled() || $this->dataHelper->isAdmin()) {
            return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setHttpResponseCode(404);
        }

        try {
            $popup = $this->popupRepository->getById((int) $this->getRequest()->getParam('id', 0));
            if (! $popup->isActive()) {
                return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setHttpResponseCode(404);
            }
        } catch (NoSuchEntityException $e) {
            return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setHttpResponseCode(404);
        }

        $this->historyManagement->logCanceling($popup);
        return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData(['error' => 0, 'messages' => []]);
    }
}
