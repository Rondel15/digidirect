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

namespace Itoris\PriceMatch\Block;

use Magento\Framework\View\Element\Template;
use Magento\Catalog\Model\Product\Type;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as TypeConfigurable;
use Magento\Customer\Model\Context;

class Product extends Template
{
    const PA_RENDER_QUEST = 1;
    const PA_RENDER_LOGIN = 2;

    protected $helperData;
    protected $registry;
    protected $localeFormat;
    protected $configurableType;
    protected $configFactory;
    protected $httpContext;
    protected $productRepository;
    protected $configurableProduct;

    private $_groupedProduct;

    public function __construct (
        Template\Context $context,
        \Itoris\PriceMatch\Helper\Data $helperData,
        \Magento\Framework\Locale\Format $localeFormat,
        \Itoris\PriceMatch\Model\ConfigFactory $configFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurableProduct,
        \Magento\ConfigurableProduct\Block\Product\View\Type\Configurable $configurableType,
        \Magento\GroupedProduct\Model\Product\Type\Grouped $groupedProduct,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->registry = $registry;
        $this->configurableProduct = $configurableProduct;
        $this->configurableType = $configurableType;
        $this->localeFormat = $localeFormat;
        $this->productRepository = $productRepository;
        $this->helperData = $helperData;
        $this->httpContext = $httpContext;
        $this->configFactory = $configFactory;

        $this->_groupedProduct = $groupedProduct;
    }

    public function checkRenderLink() {
        //if( !(\Magento\Framework\App\ObjectManager::getInstance()->get('Itoris\PriceMatch\Helper\Data')->isEnabled()) ){
        //    return false;
        //}
        return true;
    }

    public function configProduct() {
        $scopeConfig = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\App\Config\ScopeConfigInterface');
        $baseCurrency = $scopeConfig->getValue('currency/options/base', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $config = [
            'productId' => $this->_getCurrentProduct()->getId(), //$this->_request->getParam('id'),
            'storeId' => $this->_storeManager->getStore()->getId(),
            'urlAdd' => $this->getUrl('itorispm/ajax/add'),
            'urlBootstrap' => $this->getUrl('itorispm/ajax/bootstrap'),
            'priceFormat' => $this->localeFormat->getPriceFormat(null, $baseCurrency),
            'configurable_children' => []
        ];
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->_getCurrentProduct();
        if ($product->getTypeId() == TypeConfigurable::TYPE_CODE) {
            $config['cfConfig'] = $this->configurableType->getJsonConfig();

            $productTypeInstance = $product->getTypeInstance();
            $attributes = $productTypeInstance->getConfigurableAttributesAsArray($product);

            $convetrAttributes = [];

            foreach ($attributes as $opt_id => $atr) {
                $valueArrat = [];
                foreach ($atr['values'] as $value) {
                    $valueArrat[] = [$opt_id => $value['value_index']];
                }
                $convetrAttributes[] = $valueArrat;
            }

            $attrSet = $this->convertAttrArray($convetrAttributes);

            $configConfigurable = [];
            foreach ($attrSet as $atr){
                $simpleProduct = $this->configurableProduct->getProductByAttributes($atr, $product);
                $configConfigurable[] = [
                    'attribute' => $atr,
                    'product_name' => $simpleProduct->getName()
                ];
            }

            $config['configurable_children'] = $configConfigurable;
        }

//        if ($product->getTypeId() == 'grouped')
            //$condig['grouped_children'] = $this->_getGroupedProductChildrens($product);

        return \Zend_Json::encode($config);
    }

    private function _getGroupedProductChildrens($product) {
        $childs = $this->_groupedProduct->getChildrenIds($product->getId());
        
        return $childs[\Magento\GroupedProduct\Model\ResourceModel\Product\Link::LINK_TYPE_GROUPED];
    }


    /************** for debug -> remove after pass test **************/
    public function test() {
        $this->_getGroupedProductChildrens($this->_getCurrentProduct());
    }

    private function _getCurrentProduct() {
        return $this->registry->registry('current_product');
    }
    
    private function convertAttrArray($arr) {
        $result = array_shift($arr);

        foreach ($arr as $item){
            $buff = [];
            foreach ($result as $r){
                foreach ($item as $i){
                    $keyBuff = array_keys($i)[0];
                    $r[(string)$keyBuff] = $i[$keyBuff];
                    $buff[]  = $r;
                }
            }
            $result = $buff;
        }

        return $result;
    }

    public function getLink() {
        $config = $this->configFactory
                       ->create()
                       ->loadItem($this->_getCurrentProduct()->getId() /*$this->_request->getParam('id')*/, $this->getStoreId());

        return $config->getLinkText();
    }

    public function getComment() {
        return $this->helperData->getSettings($this->getStoreId())->getCommentPopup();
    }

    public function getProductName() {
        return $this->productRepository
                    ->getById($this->_getCurrentProduct()->getId() /*$this->_request->getParam('id')*/, false, $this->getStoreId())
                    ->getName();
    }

    protected function getStoreId() {
        return $this->_storeManager->getStore()->getId();
    }

    public function getCurrentProductType() {
        return $this->_getCurrentProduct()->getTypeId();
    }
}
