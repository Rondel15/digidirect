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

namespace Mageplaza\Shopbybrand\Ui\Component\Listing;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;
use Mageplaza\Shopbybrand\Model\BrandFactory;
use Mageplaza\Shopbybrand\Helper\Data;
use Magento\Catalog\Ui\Component\Listing\Columns as DefaultColumns;

/**
 * Class Columns
 * @package Mageplaza\Shopbybrand\Ui\Component\Listing
 */
class Columns
{
    /**
     * @var CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @var BrandFactory
     */
    protected $brandFactory;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @param CollectionFactory $collectionFactory
     * @param BrandFactory $brandFactory
     * @param Data $helperData
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        BrandFactory $brandFactory,
        Data $helperData
    ) {
        $this->_collectionFactory = $collectionFactory;
        $this->brandFactory       = $brandFactory;
        $this->helperData         = $helperData;
    }

    // phpcs:disable Generic.Metrics.NestingLevel
    /**
     * @param DefaultColumns $subject
     * @param string $attrCode
     * @param UiComponentInterface $component
     *
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function beforeAddComponent(DefaultColumns $subject, $attrCode, UiComponentInterface $component)
    {
        if ($this->helperData->isEnabled()) {
            $brandAttrCode = $this->helperData->getAttributeCode();

            if ($brandAttrCode === $attrCode) {
                $brandAttrLabel = '';
                $attributes     = $this->_collectionFactory->create()->addVisibleFilter();
                $config         = $component->getConfiguration();

                foreach ($attributes as $attribute) {
                    if ($attribute->getIsUserDefined()
                        && in_array(
                            $attribute->getData('frontend_input'),
                            ['select', 'swatch_visual', 'swatch_text'],
                            true
                        )
                    ) {
                        if ($attribute->getAttributeCode() == $brandAttrCode) {
                            $brandAttrLabel = $attribute->getFrontendLabel();
                        }
                    }
                }

                if (isset($config['label'])) {
                    if ($config['label'] == $brandAttrLabel) {
                        $options    = [];
                        $configShow = $this->helperData->getShowBrandInfoInAdmin();

                        foreach ($config['options'] as $option) {
                            $values   = [];
                            $optionId = '';

                            foreach ($option as $key => $value) {
                                if ($key == 'value') {
                                    $values[$key] = $value;
                                    $optionId     = $value;
                                }
                                if ($key == 'label') {
                                    $logoBrand = '';

                                    if ($optionId) {
                                        $brand         = $this->brandFactory->create()->loadByOption($optionId);
                                        $brandImageUrl = $this->helperData->getBrandImageUrl($brand);
                                        $logoBrand     = '<div class="product-brand-logo" style="text-align: center; margin: 2.5px 0;">
                                                                   <img src="' . $brandImageUrl . '"
                                                                        title="' . $value . '"
                                                                        alt="' . $value . '"
                                                                        style="width: 50px; height: auto;">
                                                              </div>';
                                    }

                                    $brandName = '<div class="product-brand-name" style="text-align: center;">
                                                           <span title="' . $value . '">' . $value . '</span>
                                                      </div>';
                                    if ($configShow == 'logo') {
                                        $values[$key] = $logoBrand;
                                    } elseif ($configShow == 'logo-name') {
                                        $values[$key] = $logoBrand . $brandName;
                                    } elseif ($configShow == 'name-logo') {
                                        $values[$key] = $brandName . $logoBrand;
                                    } else {
                                        $values[$key] = '<div class="product-brand-name">
                                                                  <span title="' . $value . '">' . $value . '</span>
                                                             </div>';
                                    }
                                }
                            }
                            $options[] = $values;
                        }

                        $config['mp_brand_labels'] = $options;
                        $config['mp_brand_attr']   = $brandAttrCode;
                        $config['component']       = 'Mageplaza_Shopbybrand/js/grid/brand';
                    }

                    $component->setData('config', $config);
                }
            }
        }

        return [$attrCode, $component];
    }
}
