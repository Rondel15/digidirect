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

namespace Itoris\PriceMatch\Controller\Adminhtml\Action;
use Magento\Backend\App\Action;
use Itoris\PriceMatch\Model\PriceMatch;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as TypeConfigurable;

class Applyform extends \Magento\Backend\App\Action
{
    private $priceMatchFactory;
    private $timezone;
    private $couponItoris;
    protected $senderCustomer;
    protected $productRepository;
    protected $configurableProduct;
    protected $priceCurrency;

    public function __construct
    (
        Action\Context $context,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \Itoris\PriceMatch\Model\PriceMatchFactory $priceMatchFactory,
        \Itoris\PriceMatch\Model\SenderCustomer $senderCustomer,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurableProduct,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Itoris\PriceMatch\Model\Coupon $couponItoris
    )
    {
        parent::__construct($context);
        $this->configurableProduct = $configurableProduct;
        $this->priceMatchFactory = $priceMatchFactory;
        $this->couponItoris = $couponItoris;
        $this->senderCustomer = $senderCustomer;
        $this->timezone = $timezone;
        $this->priceCurrency = $priceCurrency;
        $this->productRepository = $productRepository;
    }

    public function execute()
    {
        $itemId = $this->_request->getParam( 'id' );
        $method = $this->_request->getParam( 'send_method' );
        $response = $this->_request->getParam( 'send_response' );

   //     \Zend_Debug::dump(      $this->_request->getParams()      );die;

        if( $method == PriceMatch::METHOD_REJECT ){
            $this->applyReject($itemId, $response);
            $this->messageManager->addSuccess(__('The price match request has been rejected'));
        }elseif( $method == PriceMatch::METHOD_COUPON ){
            $item = $this->applyCoupon($itemId, $response);
            $diff = $item['final_price'] - $item['match_price'];
            $this->messageManager->addSuccess(__('The one-time coupon "%1" for %2 has been sent to the customer',$item['coupon_code'], $this->currencyFormat($diff, $item['store_id'])));
        }elseif( $method == PriceMatch::METHOD_LOWER ){
            $item = $this->applyLower($itemId, $response);
            $this->messageManager->addSuccess(__('Product price lowered from %1 to %2', $this->currencyFormat($item['final_price'], $item['store_id']), $this->currencyFormat($item['match_price'], $item['store_id'])));
        }
        return $this->resultRedirectFactory->create()->setPath('itorispm/index/index');
    }

    private function applyReject($id, $response=null)
    {
        $item = $this->getPriceMatch($id)
            ->setStatus(PriceMatch::STATUS_REJECTED)
            ->setMethod(PriceMatch::METHOD_REJECT)
            ->setDateResponse( $this->getCurrentDate() );
        if($response) {
            $item->setResponse( $response );
        }

        $priceMatchExtended = $this->getPriceMatchExtended($id);
        $priceMatchExtended['admin_response'] = $response;
        $priceMatchExtended = $this->calcProductName($priceMatchExtended) ;
        $this->sendEmail($priceMatchExtended, PriceMatch::METHOD_REJECT);

        $item->save();
        return $priceMatchExtended;
    }

    private function applyCoupon($id, $response=null)
    {
        /** @var \Itoris\PriceMatch\Model\PriceMatch $item */
        $item = $this->getPriceMatch($id)
            ->setStatus(PriceMatch::STATUS_APPROVED)
            ->setMethod(PriceMatch::METHOD_COUPON)
            ->setDateResponse( $this->getCurrentDate() );
        if($response) {
            $item->setResponse( $response );
        }

        $priceMatchExtended = $this->getPriceMatchExtended($id);
        $priceMatchExtended = $this->calcProductName($priceMatchExtended) ;
        $coupon = $this->couponItoris->createCoupon($priceMatchExtended);
        $priceMatchExtended['coupon_code'] = $coupon->getCode();
        $priceMatchExtended['admin_response'] = $response;
        $item->setOldPrice( $priceMatchExtended['final_price'] );
        $item->setCouponId( $coupon->getId() );
        $this->sendEmail($priceMatchExtended, PriceMatch::METHOD_COUPON);

        $item->save();
        return $priceMatchExtended;
    }

    private function applyLower($id, $response=null)
    {

        $item = $this->getPriceMatch($id)
            ->setStatus(PriceMatch::STATUS_APPROVED)
            ->setMethod(PriceMatch::METHOD_LOWER)
            ->setDateResponse( $this->getCurrentDate() );
        if($response) {
            $item->setResponse( $response );
        }

        $priceMatchExtended = $this->getPriceMatchExtended($id);
        $priceMatchExtended['admin_response'] = $response;
        $priceMatchExtended = $this->calcProductName($priceMatchExtended) ;
        $this->lowerProductPrice($priceMatchExtended);
        $item->setOldPrice( $priceMatchExtended['final_price'] );
        $this->sendEmail($priceMatchExtended, PriceMatch::METHOD_LOWER);

        $item->save();
        return $priceMatchExtended;
    }

    private function currencyFormat($amount, $storeId=0)
    {
       return $this->priceCurrency->format(
           $amount,
        false,
        \Magento\Framework\Pricing\PriceCurrencyInterface::DEFAULT_PRECISION,
           $storeId
    );
    }

    private function getCurrentDate()
    {
        return $this->timezone->date()->format('Y-m-d');
    }

    private function getPriceMatch($id)
    {
        return $this->priceMatchFactory->create()->load((int)$id);
    }

    private function getPriceMatchExtended($id)
    {
        return $this->priceMatchFactory->create()->getCollection()->sendItemById($id);
    }

    private function sendEmail($item, $method)
    {
        $this->senderCustomer->send($item, $method);
    }

    private function lowerProductPrice($item)
    {
        $product = $this->productRepository->getById($item['product_id'], false, $item['store_id']);
        if($product->getTypeId() == TypeConfigurable::TYPE_CODE ){
            $byRequest = \Zend_Json_Decoder::decode( $item['by_request'] );
            $product = $this->configurableProduct->getProductByAttributes($byRequest, $product);
        }
        $product->setPrice($item['match_price']);
        $this->productRepository->save($product);

    }

    private function calcProductName($item)
    {
        $product = $this->productRepository->getById($item['product_id'], false, $item['store_id']);
        if($product->getTypeId() == TypeConfigurable::TYPE_CODE ){
            $byRequest = \Zend_Json_Decoder::decode( $item['by_request'] );
            $product = $this->configurableProduct->getProductByAttributes($byRequest, $product);

            $item['product_name'] = $product->getName();
        }

        return $item;

    }
}