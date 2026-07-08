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
 * @category  Mageplaza
 * @package   Mageplaza_CatalogPermissions
 * @copyright Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license   https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Shopbybrand\Ui\DataProvider\Product\Modifier;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\Shopbybrand\Helper\Data;

/**
 * Class HideBrandAttribute
 * @package Mageplaza\Shopbybrand\Ui\DataProvider\Product\Modifier
 */
class HideBrandAttribute extends AbstractModifier
{
    /**
     * @var $_meta
     */
    protected $_meta;

    /**
     * @var $_hasParent
     */
    protected $_hasParent;

    /**
     * @var Configurable
     */
    protected $_catalogProductTypeConfigurable;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var Data
     */
    protected $_helperData;

    /**
     * @var StoreManagerInterface
     */
    private $_storeManager;

    /**
     * @param Configurable $catalogProductTypeConfigurable
     * @param RequestInterface $request
     * @param Data $helperData
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Configurable $catalogProductTypeConfigurable,
        RequestInterface $request,
        Data $helperData,
        StoreManagerInterface $storeManager
    ) {
        $this->_hasParent                      = false;
        $this->_catalogProductTypeConfigurable = $catalogProductTypeConfigurable;
        $this->request                         = $request;
        $this->_helperData                     = $helperData;
        $this->_storeManager                   = $storeManager;
    }

    /**
     * @param int $productId
     */
    protected function checkHasParent($productId)
    {
        $currentAction = $this->request->getFullActionName();
        $excludeAction = ['catalog_product_reload','catalog_product_new'];
        if (!in_array($currentAction, $excludeAction)) {
            $parentByChild = $this->_catalogProductTypeConfigurable->getParentIdsByChild($productId);
            if (isset($parentByChild[0])) {
                $this->_hasParent = true;
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {
        $this->_meta = $meta;
        $this->checkHasParent($this->request->getParam('id'));
        if ($this->_hasParent) {
            $this->unsetAttribute();
        }

        return $this->_meta;
    }

    /**
     * {@inheritdoc}
     *
     * @param array $data
     *
     * @return array
     */
    public function modifyData(array $data)
    {
        return $data;
    }

    /**
     * @throws NoSuchEntityException
     */
    private function unsetAttribute()
    {
        $attributeCode = $this->_helperData->getAttributeCode($this->_storeManager->getStore()->getId());
        if (isset($this->_meta['attributes']['children']['container_' . $attributeCode])) {
            unset($this->_meta['attributes']['children']['container_' . $attributeCode]);
        }
        if (isset($this->_meta['product-details']['children']['container_' . $attributeCode])) {
            unset($this->_meta['product-details']['children']['container_' . $attributeCode]);
        }
    }
}
