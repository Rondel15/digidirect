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

namespace Mageplaza\Shopbybrand\Controller\Adminhtml\Products;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\View\Result\LayoutFactory;
use Mageplaza\Shopbybrand\Block\Adminhtml\Products\ProductsGrid as Block;

/**
 * Class Gird
 * @package Mageplaza\Shopbybrand\Controller\Adminhtml\Products
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
     * @var string
     */
    protected $girdType;

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
     * @return Raw
     */
    public function execute()
    {
        $resultLayout = $this->resultLayoutFactory->create();
        $resultRaw    = $this->resultRawFactory->create();

        $productListHtml = $resultLayout->getLayout()
            ->createBlock(Block::class, 'mpShopbybrand.gird.products')->toHtml();

        return $resultRaw->setContents($productListHtml . $this->getScriptHtml());
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
                    $('#product_grid_massaction select').removeClass('required-entry');
                    $('#option_id').clone().first().appendTo('#product_grid form');
                    $('#store_id').clone().first().appendTo('#product_grid form');
                });
             </script>
SCRIPT;
    }
}
