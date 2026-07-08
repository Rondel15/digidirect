<?php

namespace Digidirect\CollectAbstractEntityMsi\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\RequestInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableType;
use Magento\GroupedProduct\Model\Product\Type\Grouped as GroupedType;
use Magento\Catalog\Model\Product\Type as SimpleType;

/**
 * Class ProductType
 * @package Digidirect\CollectAbstractEntityMsi\Helper
 */
class ProductType extends AbstractHelper
{
    /**
     * Constants
     */
    const SUPER_ATTRIBUTE = 'super_attribute';
    const SUPER_GROUP = 'super_group';
    const QTY = 'qty';
    const PRODUCT_ID = 'product_id';

    /**
     * @var array
     */
    protected $availableProductTypes = [];

    /**
     * ProductType constructor.
     * @param Context $context
     * @param array $availableProductTypes
     */
    public function __construct(
        Context $context,
        array $availableProductTypes = []
    ) {
        $this->availableProductTypes = $availableProductTypes;
        parent::__construct($context);
    }

    /**
     * @param RequestInterface $request
     * @param ProductInterface|null $product
     * @return array
     */
    public function getProductOptions(RequestInterface $request, ProductInterface $product)
    {
        $productOptions = [];
        switch ($product->getTypeId()) {
            case ConfigurableType::TYPE_CODE:
                $superAttribute = $request->getParam(self::SUPER_ATTRIBUTE);
                if (is_array($superAttribute)) {
                    $productOptions[self::SUPER_ATTRIBUTE] = $superAttribute;
                    $productOptions[self::QTY] = $request->getParam(self::QTY, 1);
                }
                break;
            case GroupedType::TYPE_CODE:
                $superGroup = $request->getParam(self::SUPER_GROUP);
                if (is_array($superGroup)) {
                    $productOptions[self::SUPER_GROUP] = $superGroup;
                }
                break;
            case SimpleType::TYPE_SIMPLE:
                $productOptions[self::QTY] = $request->getParam(self::QTY, 1);
                break;
        }
        return $productOptions;
    }

    /**
     * @return array
     */
    public function getApplicableTypes()
    {
        return $this->availableProductTypes;
    }
}
