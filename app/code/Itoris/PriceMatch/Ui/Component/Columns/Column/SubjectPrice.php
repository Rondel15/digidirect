<?php
/**
 * ITORIS
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the ITORIS's Magento Extensions License Agreement
 * which is available through the world-wide-web at this URL:
 * http://www.itoris.com/magento-extensions-license.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to sales@itoris.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extensions to newer
 * versions in the future. If you wish to customize the extension for your
 * needs please refer to the license agreement or contact sales@itoris.com for more information.
 *
 * @category   ITORIS
 * @package    ITORIS_M2_ITORIS_PRICE_MATCH
 * @copyright  Copyright (c) 2018 ITORIS INC. (http://www.itoris.com)
 * @license    http://www.itoris.com/magento-extensions-license.html  Commercial License
 */

namespace Itoris\PriceMatch\Ui\Component\Columns\Column;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;

class SubjectPrice extends \Magento\Ui\Component\Listing\Columns\Column
{
    const NAME = 'column.subject_price';

    protected $priceCurrency;
    protected $storeManager;
    protected $logger;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Psr\Log\LoggerInterface $logger,
        ProductRepositoryInterface $productRepository,
        array $components = [],
        array $data = []
    )
    {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->priceCurrency = $priceCurrency;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
        $this->productRepository = $productRepository;
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as & $item) {
                $product = $this->productRepository->get($item['product_sku']);
                $finalPrice = $product->getFinalPrice();
                $this->logger->info('prepareDataSource wiser_price, ' . $this->getCustomAttributeValue($item['product_sku'], 'wiser_price'));
                $this->logger->info('prepareDataSource final_price, ' . $finalPrice);
                $fPrice = $this->priceCurrency->format(
                    $finalPrice,
                    false,
                    \Magento\Framework\Pricing\PriceCurrencyInterface::DEFAULT_PRECISION,
                    $item['store_id']
                );

                if (isset($item[$fieldName])) {
                    $item[$fieldName] = $fPrice;
                }
            }
        }

        return $dataSource;
    }
    
    public function getCustomAttributeValue($sku, $attributeCode)
    {
        try {
            $product = $this->productRepository->get($sku);
            $attribute = $product->getCustomAttribute($attributeCode);
            return $attribute ? $attribute->getValue() : null;
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return null;
        }
    }


}