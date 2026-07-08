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

namespace Itoris\PriceMatch\Ui\DataProvider\Product\Form\Modifier;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Framework\UrlInterface;
use Magento\Ui\Component\Form\Fieldset;
use Itoris\PriceMatch\Helper\Data;
use Magento\Catalog\Model\Product\Type;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as TypeConfigurable;

class PriceMatch extends AbstractModifier
{
    const ITORIS_PRICE_MATCH = 'itoris_price_match';

    protected $locator;
    protected $arrayManager;
    protected $urlBuilder;
    protected $meta = [];
    protected $_helper;

    public function __construct(
        LocatorInterface $locator,
        ArrayManager $arrayManager,
        UrlInterface $urlBuilder,
        Data $helper
    ) {
        $this->locator = $locator;
        $this->_helper = $helper;
        $this->arrayManager = $arrayManager;
        $this->urlBuilder = $urlBuilder;
    }

    public function modifyData(array $data)
    {
        return array_replace_recursive($data, []);
    }

    public function modifyMeta(array $meta)
    {
        if(!$this->_helper->isEnabled()){
            return  $meta;
        }
        $this->meta = $meta;

        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->locator->getProduct();
        $typeProduct = $product->getTypeId();

        if($typeProduct == Type::TYPE_VIRTUAL || $typeProduct == Type::TYPE_SIMPLE || $typeProduct == TypeConfigurable::TYPE_CODE)
        {
            $this->addFieldset();
        }

        return $this->meta;
    }

    protected function addFieldset()
    {
        $this->meta = array_replace_recursive(
            $this->meta,
            [
                static::ITORIS_PRICE_MATCH => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'label' => __('Price Match'),
                                'componentType' => Fieldset::NAME,
                                'dataScope' => 'data.product',
                                'collapsible' => true,
                                'sortOrder' => 31,
                            ],
                        ],
                    ],
                    'children' => [
                        'itoris_price_match_fieldset_block' => $this->addForm(),
                    ],

                ],
            ]
        );

        return $this;
    }

    public function addForm()
    {
        $block = $this->_helper->getBlock('Itoris\PriceMatch\Block\Adminhtml\Product\Form\Content');
        return [
            'children' => [
                'spc'=> [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'label' => null,
                                'formElement' => \Magento\Ui\Component\Container::NAME,
                                'componentType' => \Magento\Ui\Component\Container::NAME,
                                'template' => 'ui/form/components/complex',
                                'sortOrder' => 10,
                                'content' => $block->toHtml(),
                            ],
                        ],
                    ],
                ],

            ],
            'arguments' => [
                'data' => [
                    'config' => [
                        'additionalClasses' => 'admin__fieldset-section',
                        'label' => false,
                        'collapsible' => false,
                        'componentType' => Fieldset::NAME,
                        'dataScope' => '',
                        'sortOrder' => 10,
                    ],
                ],
            ],
        ];
    }

}