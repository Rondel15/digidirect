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

namespace Itoris\PriceMatch\Ui\DataProvider\Admin\Edit;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\ReportingInterface;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\App\RequestInterface;

class DataProvider extends \Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider
{
    protected $collectionFactory;
    protected $request;
    protected $priceCurrency;
    protected $couponFactory;
    protected $escaper;
    protected $urlBuilder;
    protected $configurableProduct;
    protected $productFactory;
    protected $productRepository;

    public function __construct
    (
        $name,
        $primaryFieldName,
        $requestFieldName,
        ReportingInterface $reporting,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\SalesRule\Model\CouponFactory $couponFactory,
        \Magento\Framework\Escaper $escaper,
        \Magento\Backend\Model\UrlInterface $urlBuilder,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurableProduct,
        \Itoris\PriceMatch\Model\ResourceModel\PriceMatch\CollectionFactory $collectionFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,

        RequestInterface $request,
        FilterBuilder $filterBuilder,
        array $meta = [],
        array $data = []
    )
    {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $reporting, $searchCriteriaBuilder, $request, $filterBuilder, $meta, $data);
        $this->collectionFactory = $collectionFactory;
        $this->request = $request;
        $this->escaper = $escaper;
        $this->productFactory = $productFactory;
        $this->configurableProduct = $configurableProduct;
        $this->urlBuilder = $urlBuilder;
        $this->couponFactory = $couponFactory;
        $this->priceCurrency = $priceCurrency;
        $this->productRepository = $productRepository;
    }

    public function getData()
    {
        $priceMatch = $this->collectionFactory->create();
        $item = $priceMatch->sendItemById($this->request->getParam('id'));
        $methodLabel = '';

        if($item['method'] == \Itoris\PriceMatch\Model\PriceMatch::METHOD_LOWER){
            $methodLabel = __('Product price lowered from %1 to %2', $this->formatPrice($item['old_price'], $item['store_id']), $this->formatPrice($item['match_price'], $item['store_id']));
        }elseif($item['method'] == \Itoris\PriceMatch\Model\PriceMatch::METHOD_COUPON){
            $diff = $item['old_price'] - $item['match_price'];
            $diff = $this->formatPrice($diff, $item['store_id']);

            $couponCode = $this->couponFactory->create()->load($item['coupon_id'])->getCode();
            $methodLabel = __('The one-time coupon "%1" for %2 has been sent to the customer',$couponCode, $diff);
        }elseif ($item['method'] == \Itoris\PriceMatch\Model\PriceMatch::METHOD_REJECT){
            $methodLabel = __('Rejected');
        }

        if( $item['by_request'] ){
            $byRequest = \Zend_Json_Decoder::decode( $item['by_request'] );
            $product = $this->productFactory->create()->load( $item['product_id'] );
            $simpleProduct = $this->configurableProduct->getProductByAttributes($byRequest, $product);
            $item['product_name'] = $simpleProduct->getName();

        }

        return [
            $item['item_id']=>[
                'item_id'        => $item['item_id'],
                'customer_name'  => $this->_formatCustomerName($item),
                'date_created'   => $item['date_created'],
                'customer_email' => $this->_formatCustomerMail($item),
                'product_name'   => $this->_formatProductName($item),
                'product_sku'   => $this->_formatProductSku($item),
                'match_price'    => $this->_formatMatchPrice($item),
                'status'         => $item['status'],
                'final_price'    => $this->_formatFinalPrice($item),
                'comment'        => $this->escaper->escapeHtml($item['comment']),
                'match_url'      => '<a href="'.$item['match_url'].'" target="_blank">'.$item['match_url'].'</a>',
                'date_response'  => $this->escaper->escapeHtml($item['date_response']),
                'response'       => $item['response'] ? $this->escaper->escapeHtml($item['response']) : __('n/a'),
                'method'         =>$methodLabel,

            ]
        ];
    }

    private function _formatCustomerName($item) {
        $customerName = $this->escaper->escapeHtml($item['customer_name']);
        if ($item['customer_id'] === null)
            return $customerName;
        $customerUrl = $this->urlBuilder->getUrl('customer/index/edit', ['id'=>$item['customer_id']]);

        return '<a href="' . $customerUrl . '">' . $customerName . '</a>';
    }

    private function _formatCustomerMail($item) {
        return '<a href="mailto:' . $item['customer_email'] . '">' . $item['customer_email'] . '</a>';
    }

    private function _formatProductName($item) {
        $productUrl = $this->urlBuilder->getUrl('catalog/product/edit', ['id'=>$item['product_id']]);
        return '<a href="'.$productUrl.'" target="_blank">'.$item['product_name'].'</a>';
    }

    private function _formatProductSku($item) {
        $productUrl = $this->urlBuilder->getUrl('catalog/product/edit', ['id'=>$item['product_id']]);
        return $item['product_sku'];
    }

    private function _formatMatchPrice($item) {
        return $this->formatPrice($item['match_price'], $item['store_id']);
    }

    private function _formatFinalPrice($item) {

//        $product = $this->productFactory->create()->load( $item['product_id'] );
//        //$finalPrice = $product->getFinalPrice();
//        $finalPrice = $product->getPriceInfo()->getPrice('final_price')->getAmount()->getValue();

        $product = $this->productRepository->getById($item['product_id']);
        $price = $product->getCustomAttribute('custom_final_price');
        //$finalPrice = $price->getValue();
        $finalPrice = $product->getFinalPrice();
        $wiserPrice = $product->getData('wiser_price');
        
        //$this->logger->info("wiserPrice: ". $wiserPrice);
        //$this->logger->info("finalPrice: ". $finalPrice);

        /*if ($wiserPrice != 0 || !empty($wiserPrice)) {
            if($wiserPrice < $finalPrice) {
                $finalPrice = $wiserPrice;
            }
        }*/

        return $this->formatPrice($finalPrice, $item['store_id']);
        //return $this->formatPrice($item['final_price'], $item['store_id']);
    }

    private function formatPrice($amount, $store){
        return $this->priceCurrency->format(
            $amount,
            false,
            \Magento\Framework\Pricing\PriceCurrencyInterface::DEFAULT_PRECISION,
            $store
        );
    }
}
