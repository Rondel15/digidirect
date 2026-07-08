<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\Newsletterpopup\ViewModel\Popup;

use Magento\Bundle\Model\Product\Type as BundleProductType;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\CatalogRule\Model\Indexer\ProductPriceCalculator;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\SalesRule\Api\RuleRepositoryInterface;
use Plumrocket\Newsletterpopup\Block\Popup\Product as ProductBlock;
use Plumrocket\Newsletterpopup\Model\Popup;
use Plumrocket\Newsletterpopup\Model\Popup\Variable\Placeholder;

/**
 * @since 4.0.0
 */
class Product
{
    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    private $layout;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    private $imageHelper;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var bool[]|\Magento\SalesRule\Api\Data\RuleInterface[]
     */
    private $ruleDataModel = [];

    /**
     * @var array
     */
    private $allowedTypes = ['by_fixed', 'by_percent'];

    /**
     * @var \Magento\CatalogRule\Model\Indexer\ProductPriceCalculator
     */
    private $productPriceCalculator;

    /**
     * @var \Magento\SalesRule\Api\RuleRepositoryInterface
     */
    private $ruleRepository;

    /**
     * @var \Plumrocket\Newsletterpopup\Model\Popup\Variable\Product
     */
    private $productVariables;

    /**
     * @var \Plumrocket\Newsletterpopup\Model\Popup\Variable\Placeholder
     */
    private $variablePlaceholder;

    /**
     * @param \Magento\Framework\View\LayoutInterface                      $layout
     * @param \Magento\Catalog\Helper\Image                                $imageHelper
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface            $priceCurrency
     * @param \Magento\CatalogRule\Model\Indexer\ProductPriceCalculator    $productPriceCalculator
     * @param \Magento\SalesRule\Api\RuleRepositoryInterface               $ruleRepository
     * @param \Plumrocket\Newsletterpopup\Model\Popup\Variable\Product     $productVariables
     * @param \Plumrocket\Newsletterpopup\Model\Popup\Variable\Placeholder $variablePlaceholder
     */
    public function __construct(
        LayoutInterface $layout,
        ImageHelper $imageHelper,
        PriceCurrencyInterface $priceCurrency,
        ProductPriceCalculator $productPriceCalculator,
        RuleRepositoryInterface $ruleRepository,
        Popup\Variable\Product $productVariables,
        Placeholder $variablePlaceholder
    ) {
        $this->layout = $layout;
        $this->imageHelper = $imageHelper;
        $this->priceCurrency = $priceCurrency;
        $this->productPriceCalculator = $productPriceCalculator;
        $this->ruleRepository = $ruleRepository;
        $this->productVariables = $productVariables;
        $this->variablePlaceholder = $variablePlaceholder;
    }

    public function addVariables(array $variablesData, \Plumrocket\Newsletterpopup\Block\Popup $popupBlock): array
    {
        $data = array_fill_keys($this->productVariables->getVariablesCode(), '');
        $popup = $popupBlock->getPopup();

        if ($popup->getId() &&
            $this->variablePlaceholder->hasProductPlaceholder($popup) &&
            $popupBlock->getProduct()
        ) {
            $product = $popupBlock->getProduct();
            $popup = $popupBlock->getPopup();
            if ($this->variablePlaceholder->hasPlaceholder('{{product_template}}', $popup)) {
                $data['{{product_template}}'] = $this->getProductTemplateHtml(
                    $popupBlock->getPopup(),
                    $product
                );
            }

            $data['{{product_name}}'] = $product->getName();
            $data['{{product_sku}}'] = $product->getSku();
            $data['{{product_small_image_url}}'] = $this->imageHelper
                ->init($product, 'product_small_image')
                ->getUrl();

            $data['{{product_image_url}}'] = $this->imageHelper
                ->init($product, 'product_page_image_medium')
                ->getUrl();
            $data['{{product_url}}'] = $product->getProductUrl();

            if ($product->getTypeId() === BundleProductType::TYPE_CODE) {
                /** @var \Magento\Bundle\Pricing\Price\BundleRegularPrice $finalPriceModel */
                $finalPriceModel = $product->getPriceInfo()->getPrice('regular_price');
                $price = $finalPriceModel->getMaximalPrice()->getValue();
            } else {
                $price = $product->getFinalPrice();
            }

            $data['{{product_price}}'] = $this->priceCurrency->format($price, false);

            if ($ruleDataModel = $this->getRuleDataModel($popup)) {
                $salePrice = $this->getSalePrice($popup, $product);
                $amountDiscount = $price - $salePrice;
                if ($ruleDataModel->getSimpleAction() === 'by_percent') {
                    $percentDiscount = $ruleDataModel->getDiscountAmount();
                } else {
                    $percentDiscount = ceil($salePrice / $price * 100);
                }

                $data['{{product_sale_price}}'] = $this->priceCurrency->format($salePrice, false);
                $data['{{product_discount_amount}}'] = $this->priceCurrency->format($amountDiscount, false, 0);
                $data['{{product_discount_percent}}'] = $percentDiscount . '%';
                $data['{{product_formatted_sale}}'] = $this->getFormattedSale($popup);
            }
        }

        return array_merge($variablesData, $data);
    }

    /**
     * @param \Plumrocket\Newsletterpopup\Model\Popup    $popup
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @return string
     */
    public function getProductTemplateHtml(Popup $popup, ProductInterface $product): string
    {
        /** @var ProductBlock $block */
        $block = $this->layout->createBlock(ProductBlock::class);

        return $block->setPopup($popup)
                     ->setProduct($product)
                     ->setData('viewModel', $this)
                     ->toHtml();
    }

    /**
     * @return array
     */
    public function getAllowedTypes(): array
    {
        return $this->allowedTypes;
    }

    /**
     * Retrieve price of product
     * Use final price
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @return mixed
     */
    public function getPrice(ProductInterface $product)
    {
        return $product->getFinalPrice();
    }

    /**
     * Retrieve product price after apply rule discount
     *
     * @param \Plumrocket\Newsletterpopup\Model\Popup    $popup
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @return float
     */
    public function getSalePrice(Popup $popup, ProductInterface $product)
    {
        if ($product->getTypeId() === BundleProductType::TYPE_CODE) {
            /** @var \Magento\Bundle\Pricing\Price\FinalPrice $finalPriceModel */
            $finalPriceModel = $product->getPriceInfo()->getPrice('final_price');
            $price = $finalPriceModel->getMaximalPrice()->getValue();
        } else {
            $price = $product->getFinalPrice();
        }

        return $this->productPriceCalculator->calculate(
            [
                'action_operator' => $this->getRuleDataModel($popup)->getSimpleAction(),
                'action_amount'   => $this->getRuleDataModel($popup)->getDiscountAmount(),
            ],
            [
                'rule_price' => $price
            ]
        );
    }

    /**
     * @param float $price
     * @return string
     */
    public function formatPrice($price)
    {
        return $this->priceCurrency->format($price, false);
    }

    /**
     * @param \Plumrocket\Newsletterpopup\Model\Popup $popup
     * @return bool|float|string
     */
    public function getFormattedSale(Popup $popup)
    {
        $ruleDataModel = $this->getRuleDataModel($popup);

        return $this->formatSale($ruleDataModel->getSimpleAction(), $ruleDataModel->getDiscountAmount());
    }

    /**
     * @param $type
     * @param $amount
     * @return bool|float|string
     */
    public function formatSale($type, $amount)
    {
        switch ($type) {
            case 'to_fixed':
            case 'by_fixed':
                $sale = $this->formatPrice($amount);
                break;
            case 'to_percent':
            case 'by_percent':
                $sale = (int) $amount . '%';
                break;
            default:
                $sale = false;
        }

        return $sale;
    }

    /**
     * @param \Plumrocket\Newsletterpopup\Model\Popup $popup
     * @return bool|\Magento\SalesRule\Api\Data\RuleInterface
     */
    private function getRuleDataModel(Popup $popup)
    {
        if (! isset($this->ruleDataModel[$popup->getId()])) {
            try {
                $this->ruleDataModel[$popup->getId()] = $this->ruleRepository->getById($popup->getCouponCode());
            } catch (NoSuchEntityException $e) {
                $this->ruleDataModel[$popup->getId()] = false;
            }
        }

        return $this->ruleDataModel[$popup->getId()];
    }

    /**
     * @param \Plumrocket\Newsletterpopup\Model\Popup $popup
     * @return bool
     */
    public function hasSimpleCoupon(Popup $popup): bool
    {
        if ($ruleDataModel = $this->getRuleDataModel($popup)) {
            return in_array($ruleDataModel->getSimpleAction(), $this->getAllowedTypes(), true);
        }

        return false;
    }
}
