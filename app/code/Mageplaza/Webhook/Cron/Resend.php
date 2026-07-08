<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_Webhook
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Webhook\Cron;

use Exception;
use Mageplaza\Webhook\Helper\Data;
use Mageplaza\Webhook\Model\Config\Source\Status;
use Mageplaza\Webhook\Model\HookFactory;
use Mageplaza\Webhook\Model\ResourceModel\History\CollectionFactory;

/**
 * Class Resend
 * @package Mageplaza\Webhook\Cron
 */
class Resend
{
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var HookFactory
     */
    protected $hookFactory;

    /**
     * Resend constructor.
     *
     * @param CollectionFactory $collectionFactory
     * @param Data $data
     * @param HookFactory $hookFactory
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        Data $data,
        HookFactory $hookFactory
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->helper            = $data;
        $this->hookFactory       = $hookFactory;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $histories = $this->collectionFactory->create()->addFieldToFilter('status', 0);
        foreach ($histories as $history) {
            try {
                $hookId = $history->getHookId();
                $hook   = $this->hookFactory->create()->load($hookId);
                $result = $this->helper->sendHttpRequestFromHook($hook, false, $history);
                $history->setResponse($result['response']);
            } catch (Exception $e) {
                $result = [
                    'success' => false,
                    'message' => $e->getMessage()
                ];
            }
            if ($result['success'] === true) {
                $history->setStatus(Status::SUCCESS)->setMessage('');
            } else {
                $message = __('Cannot replay the log, Please try again later.');
                $history->setStatus(Status::ERROR)->setMessage($message);
            }
            $history->save();
        }
    }
}
