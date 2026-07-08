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

namespace Mageplaza\Webhook\Observer;

use Exception;
use Magento\Framework\Event\Observer;
Use Magento\Review\Model\Review as ReviewModel;
use Mageplaza\Webhook\Model\Config\Source\HookType;

/**
 * Class Reviews
 * @package Mageplaza\Webhook\Observer
 */
class Reviews extends AfterSave
{
    /**
     * @var string
     */
    protected $hookType = HookType::New_REVIEWS;

    /**
     * @param Observer $observer
     *
     * @return $this|void
     * @throws Exception
     */
    public function execute(Observer $observer)
    {
        if (!$this->helper->isEnabled()) {
            return $this;
        }
        $item         = $observer->getEvent()->getDataObject();
        $reviewStatus = $item->getStatusId();

        if (in_array($reviewStatus, [ReviewModel::STATUS_PENDING, ReviewModel::STATUS_NOT_APPROVED])
            || $item->getReviewId()
        ) {
            return $this;
        }

        parent::execute($observer);
    }
}
