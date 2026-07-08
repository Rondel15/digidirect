<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\Newsletterpopup\Controller\Adminhtml\Popups;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\SalesRule\Api\RuleRepositoryInterface;
use Plumrocket\Newsletterpopup\Helper\Adminhtml;
use Plumrocket\Newsletterpopup\Helper\DateTime;
use Psr\Log\LoggerInterface;

/**
 * @since 4.0.0
 */
class CouponData extends \Magento\Backend\App\Action
{

    /**
     * @var \Plumrocket\Newsletterpopup\Helper\Adminhtml
     */
    protected $_adminhtmlHelper;

    /**
     * @var \Magento\SalesRule\Api\RuleRepositoryInterface
     */
    private $ruleRepository;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Plumrocket\Newsletterpopup\Helper\DateTime
     */
    private $dateTime;

    /**
     * @param \Magento\Backend\App\Action\Context            $context
     * @param \Plumrocket\Newsletterpopup\Helper\Adminhtml   $adminhtmlHelper
     * @param \Magento\SalesRule\Api\RuleRepositoryInterface $ruleRepository
     * @param \Psr\Log\LoggerInterface                       $logger
     * @param \Plumrocket\Newsletterpopup\Helper\DateTime    $dateTime
     */
    public function __construct(
        Context $context,
        Adminhtml $adminhtmlHelper,
        RuleRepositoryInterface $ruleRepository,
        LoggerInterface $logger,
        DateTime $dateTime
    ) {
        parent::__construct($context);
        $this->_adminhtmlHelper = $adminhtmlHelper;
        $this->ruleRepository = $ruleRepository;
        $this->logger = $logger;
        $this->dateTime = $dateTime;
    }

    /**
     * Get coupon data.
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $jsonResult */
        $jsonResult = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        try {
            $ruleId = (int) $this->getRequest()->getParam('ruleId');

            $couponParams = $this->ruleRepository->getById($ruleId);

            $startDate = strtotime((string) $couponParams->getFromDate());
            $endDate = strtotime((string) $couponParams->getToDate());

            $response = [
                'from_date' => $startDate ? $this->dateTime->format($startDate, 'MM/dd/YYYY hh:mm a') : '',
                'to_date' => $endDate ? $this->dateTime->format($endDate, 'MM/dd/YYYY hh:mm a') : '',
                'use_auto_generation' => $couponParams->getUseAutoGeneration() ?: '',
            ];

            return $jsonResult->setData($response);
        } catch (NoSuchEntityException|LocalizedException $e) {
            $this->logger->error($e->getMessage());
            return $jsonResult->setData([]);
        }
    }
}
