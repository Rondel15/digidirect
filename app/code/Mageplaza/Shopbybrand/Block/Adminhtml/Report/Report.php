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

namespace Mageplaza\Shopbybrand\Block\Adminhtml\Report;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Template;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Mageplaza\Shopbybrand\Helper\Data as BrandHelper;

/**
 * Class Report
 * @package Mageplaza\Shopbybrand\Block\Adminhtml\Report
 */
class Report extends Template
{
    /**
     * @var BrandHelper
     */
    protected $brandHelper;

    /**
     * Report constructor.
     *
     * @param Context $context
     * @param BrandHelper $helper
     * @param array $data
     * @param JsonHelper|null $jsonHelper
     * @param DirectoryHelper|null $directoryHelper
     */
    public function __construct(
        Context $context,
        BrandHelper $helper,
        array $data = [],
        ?JsonHelper $jsonHelper = null,
        ?DirectoryHelper $directoryHelper = null
    ) {
        $this->brandHelper = $helper;

        parent::__construct($context, $data, $jsonHelper, $directoryHelper);
    }

    /**
     * return array
     */
    public function getDateRange()
    {
        $dateRange = $this->brandHelper->getDateRange();

        if ($startDate = $this->getRequest()->getParam('startDate')) {
            $dateRange[0] = $startDate;
        }
        if ($endDate = $this->getRequest()->getParam('endDate')) {
            $dateRange[1] = $endDate;
        }

        return $dateRange;
    }

    /**
     * @return string
     */
    public function getReportGridUrl()
    {
        return $this->getBaseUrl() . 'admin/mpbrand/report/reportgrid';
    }

    /**
     * @return string
     */
    public function getReportItemDataUrl()
    {
        return $this->getBaseUrl() . 'admin/mpbrand/report/itemdata';
    }

    /**
     * @return BrandHelper
     */
    public function getHelperData()
    {
        return $this->brandHelper;
    }
}
