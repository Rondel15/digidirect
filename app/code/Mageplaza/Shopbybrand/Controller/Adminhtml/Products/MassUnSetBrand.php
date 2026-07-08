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

use Magento\Backend\App\Action\Context;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\View\Result\LayoutFactory;
use Mageplaza\Core\Helper\AbstractData;
use Mageplaza\Shopbybrand\Helper\Data as BrandHelper;

/**
 * Class MassUnSetBrand
 * @package Mageplaza\Shopbybrand\Controller\Adminhtml\Products
 */
class MassUnSetBrand extends Gird
{
    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;
    /**
     * @var BrandHelper
     */
    protected $brandHelper;
    /**
     * @var IndexerRegistry
     */
    protected $indexerRegistry;

    /**
     * MassUnSetBrand constructor.
     *
     * @param Context $context
     * @param LayoutFactory $resultLayoutFactory
     * @param RawFactory $resultRawFactory
     * @param ProductRepositoryInterface $productRepository
     * @param BrandHelper $helper
     * @param IndexerRegistry $indexerRegistry
     */
    public function __construct(
        Context $context,
        LayoutFactory $resultLayoutFactory,
        RawFactory $resultRawFactory,
        ProductRepositoryInterface $productRepository,
        BrandHelper $helper,
        IndexerRegistry $indexerRegistry
    ) {
        $this->productRepository = $productRepository;
        $this->brandHelper       = $helper;
        $this->indexerRegistry   = $indexerRegistry;

        parent::__construct($context, $resultLayoutFactory, $resultRawFactory);
    }

    /**
     * @return Raw
     */
    public function execute()
    {
        $resultRaw         = $this->resultRawFactory->create();
        $storeId           = $this->getRequest()->getParam('store_id');
        $result['success'] = true;
        if ($productIds = $this->getRequest()->getParam('entity_id')) {
            foreach ($productIds as $productId) {
                try {
                    $this->unSetBrand($productId, $storeId);
                } catch (NoSuchEntityException $e) {
                    $result['success'] = false;
                    $result['message'] = $e->getMessage();
                }
            }
        }
        $this->indexProducts($productIds);

        return $resultRaw->setContents(AbstractData::jsonEncode($result));
    }

    /**
     * set Attribute Value is null
     *
     * @param int $productId
     * @param int $storeId
     *
     * @throws NoSuchEntityException
     */
    public function unSetBrand($productId, $storeId)
    {
        $product = $this->productRepository->getById($productId);
        $product->addAttributeUpdate($this->brandHelper->getAttributeCode($storeId), null, $storeId);
    }

    /**
     * Index the catalogsearch_fulltext by list Products to show Products in Brand View page.
     *
     * @param $productIds
     */
    protected function indexProducts($productIds)
    {
        $indexer = $this->indexerRegistry->get('catalogsearch_fulltext');
        if ($indexer) {
            $indexer->reindexList($productIds);
        }
    }
}
