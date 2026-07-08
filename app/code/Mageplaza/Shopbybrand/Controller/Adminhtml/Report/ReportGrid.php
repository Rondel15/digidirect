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
use Magento\Framework\Registry;
use Mageplaza\Shopbybrand\Block\Adminhtml\Report\Report as ReportBlock;
use Mageplaza\Shopbybrand\Block\Adminhtml\Report\ReportGrid as ReportGridBlock;

/**
 * Class ReportGrid
 * @package Mageplaza\Shopbybrand\Controller\Adminhtml\Report
 */
class ReportGrid extends Action
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
     * @var ReportBlock
     */
    protected $reportBlock;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param LayoutFactory $resultLayoutFactory
     * @param QuoteSession $quoteSession
     * @param Registry $registry
     * @param ReportBlock $reportBlock
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        LayoutFactory $resultLayoutFactory,
        QuoteSession $quoteSession,
        Registry $registry,
        ReportBlock $reportBlock
    ) {
        $this->resultLayoutFactory = $resultLayoutFactory;
        $this->resultJsonFactory   = $resultJsonFactory;
        $this->quoteSession        = $quoteSession;
        $this->registry            = $registry;
        $this->reportBlock         = $reportBlock;

        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|Json|ResultInterface
     */
    public function execute()
    {
        $resultLayout = $this->resultLayoutFactory->create();
        $result       = $this->resultJsonFactory->create();
        $filterData   = $this->reportBlock->getDateRange();

        $this->registry->register('mpShopbybrand_date', $filterData);

        $gridHtml = $resultLayout->getLayout()
            ->createBlock(
                ReportGridBlock::class,
                'mpShopbybrand.report.grid'
            )
            ->toHtml();

        $result->setData(['success' => $gridHtml]);

        return $result;
    }
}
