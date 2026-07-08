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

namespace Mageplaza\Shopbybrand\Controller\Adminhtml\Related;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\LayoutFactory;
use Mageplaza\Shopbybrand\Block\Adminhtml\Related\BrandsGrid as Block;

/**
 * Class Gird
 * @package Mageplaza\Shopbybrand\Controller\Adminhtml\Related
 */
class Gird extends Action
{
    /**
     * @var LayoutFactory
     */
    protected $resultLayoutFactory;

    /**
     * @var RawFactory
     */
    protected $resultRawFactory;

    /**
     * Gird constructor.
     *
     * @param Context $context
     * @param LayoutFactory $resultLayoutFactory
     * @param RawFactory $resultRawFactory
     */
    public function __construct(
        Context $context,
        LayoutFactory $resultLayoutFactory,
        RawFactory $resultRawFactory
    ) {
        $this->resultLayoutFactory = $resultLayoutFactory;
        $this->resultRawFactory    = $resultRawFactory;

        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|Raw|ResultInterface
     */
    public function execute()
    {
        $resultLayout = $this->resultLayoutFactory->create();
        $resultRaw    = $this->resultRawFactory->create();

        $brandListHtml = $resultLayout->getLayout()
                ->createBlock(Block::class, 'mpShopbybrand.gird.related')
                ->setOptionId($this->_request->getParam('option_id'))
                ->toHtml() . $this->getScriptHtml();

        return $resultRaw->setContents($brandListHtml);
    }

    /**
     * @return string
     */
    public function getScriptHtml()
    {
        return <<<SCRIPT
        <script type="text/javascript">
            require([
                'jquery'
            ], function ($) {
                var girId = '#mpbrand-grid-related_table ', relatedBrands = $("#related_brands").val();
                  $(girId + ' tbody tr input').each(function () {
                     if ($(this).attr('value') && relatedBrands.includes($(this).attr('value'))) {
                       $(this).attr('checked', 'checked');// add checked true with related brands
                     }
                   });
            });
        </script>
        SCRIPT;
    }
}
