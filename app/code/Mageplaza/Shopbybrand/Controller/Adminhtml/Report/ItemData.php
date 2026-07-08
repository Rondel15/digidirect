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
 * @package     Mageplaza_Shopbybrand
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Shopbybrand\Controller\Adminhtml\Report;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Session\Quote as QuoteSession;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\LayoutFactory;
use Mageplaza\Shopbybrand\Model\ResourceModel\Grid\Report\CollectionFactory;
use Mageplaza\Shopbybrand\Helper\Data as BrandHelper;

/**
 * Class ItemData
 * @package Mageplaza\Shopbybrand\Controller\Adminhtml\Report
 */
class ItemData extends Action
{
    /**
     * @var LayoutFactory
     */
    protected $resultLayoutFactory;

    /**
     * @var QuoteSession
     */
    protected $quoteSession;

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var Report
     */
    protected $brandReport;

    /**
     * @var BrandHelper
     */
    protected $brandHelper;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * ItemData constructor.
     *
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param LayoutFactory $resultLayoutFactory
     * @param QuoteSession $quoteSession
     * @param CollectionFactory $collectionFactory
     * @param BrandHelper $helper
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        LayoutFactory $resultLayoutFactory,
        QuoteSession $quoteSession,
        CollectionFactory $collectionFactory,
        BrandHelper $helper
    ) {
        $this->resultLayoutFactory = $resultLayoutFactory;
        $this->resultJsonFactory   = $resultJsonFactory;
        $this->quoteSession        = $quoteSession;
        $this->collectionFactory   = $collectionFactory;
        $this->brandHelper         = $helper;

        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|Json|ResultInterface
     */
    public function execute()
    {
        $result    = $this->resultJsonFactory->create();
        $dateRange = $this->brandHelper->getDateRange();

        if ($startDate = $this->getRequest()->getParam('startDate')) {
            $dateRange[0] = $startDate;
        }
        if ($endDate = $this->getRequest()->getParam('endDate')) {
            $dateRange[1] = $endDate;
        }

        $data = $this->getItemData($dateRange);
        $result->setData(['success' => $data]);

        return $result;
    }

    /**
     * @param $dateRange
     *
     * @return array
     */
    public function getItemData($dateRange)
    {

        $fromDate   = $dateRange[0];
        $toDate     = $dateRange[1];
        $collection = $this->collectionFactory->create();

        if ($fromDate !== null) {
            $collection->addFieldToFilter('order_created_at', ['gteq' => $fromDate]);
        }
        if ($toDate !== null) {
            $collection->addFieldToFilter('order_created_at', ['lteq' => $toDate]);
        }

        $items = [];
        foreach ($collection->getItems() as $item) {
            if ($item->getData('attribute_name') !== null && (int) $item->getData('total') > 0) {
                $items[] = $item->getData();
            }
        }

        $data = ['items' => $items, 'totalRecords' => count($collection->getItems())];

        return $data;
    }
}
