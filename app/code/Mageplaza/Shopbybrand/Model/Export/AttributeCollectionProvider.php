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

declare(strict_types=1);

namespace Mageplaza\Shopbybrand\Model\Export;

use Exception;
use Magento\Eav\Model\Entity\AttributeFactory;
use Magento\ImportExport\Model\Export\Factory as CollectionFactory;
use Mageplaza\Shopbybrand\Model\Export\Source\EnableDisable;
use Mageplaza\Shopbybrand\Model\Export\Source\YesNo;
use Mageplaza\Shopbybrand\Model\Import\Brand as BrandImport;
use Mageplaza\Shopbybrand\Model\ResourceModel\Export\Collection;

/**
 * Class AttributeCollectionProvider
 * @package Mageplaza\Shopbybrand\Model\Export
 */
class AttributeCollectionProvider
{

    const OPTION_ID   = 'option_id';
    const COL_DISPLAY = 'is_display';

    /**
     * @var \Magento\Framework\Data\Collection
     */
    private $collection;

    /**
     * @var AttributeFactory
     */
    private $attributeFactory;

    /**
     * @param CollectionFactory $collectionFactory
     * @param AttributeFactory $attributeFactory
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        AttributeFactory $attributeFactory
    ) {
        $this->collection       = $collectionFactory->create(Collection::class);
        $this->attributeFactory = $attributeFactory;
    }

    /**
     * @return \Magento\Framework\Data\Collection
     * @throws Exception
     */
    public function get()
    {
        $listAttributes = [
            [
                'code'  => BrandImport::COL_NAME,
                'label' => 'Brand Name',
                'type'  => 'varchar'
            ],
            [
                'code'  => BrandImport::COL_STORE_ID,
                'label' => 'Store ID',
                'type'  => 'int'
            ],
            [
                'code'  => BrandImport::COL_URL_KEY,
                'label' => 'Url Key',
                'type'  => 'varchar'
            ],
            [
                'code'  => BrandImport::COL_VALUE,
                'label' => 'Label',
                'type'  => 'varchar'
            ],
            [
                'code'  => BrandImport::COL_IMAGE,
                'label' => 'Image',
                'type'  => 'varchar'
            ],
            [
                'code'           => BrandImport::COL_FEATURED,
                'label'          => 'Is Featured',
                'type'           => 'int',
                'frontend_input' => 'select'
            ],
            [
                'code'           => self::COL_DISPLAY,
                'label'          => 'Is Display',
                'type'           => 'int',
                'frontend_input' => 'select'
            ],
            [
                'code'  => self::OPTION_ID,
                'label' => 'Option ID',
                'type'  => 'varchar'
            ],
            [
                'code'  => BrandImport::COL_SHORT_DESCRIPTION,
                'label' => 'Short Description',
                'type'  => 'varchar'
            ],
            [
                'code'  => BrandImport::COL_DESCRIPTION,
                'label' => 'Description',
                'type'  => 'varchar'
            ],
            [
                'code'  => BrandImport::COL_STATIC_BLOCK,
                'label' => 'Static Block',
                'type'  => 'varchar'
            ],
            [
                'code'  => BrandImport::COL_META_TITLE,
                'label' => 'Meta Title',
                'type'  => 'varchar'
            ],
            [
                'code'  => BrandImport::COL_META_KEYWORDS,
                'label' => 'Meta Keywords',
                'type'  => 'varchar'
            ],
            [
                'code'  => BrandImport::COL_META_DESCRIPTION,
                'label' => 'Meta Description',
                'type'  => 'varchar'
            ],
            [
                'code'  => BrandImport::COL_SWATCH_TYPE,
                'label' => 'Swatch Type',
                'type'  => 'varchar'
            ],
            [
                'code'  => BrandImport::COL_SWATCH_VALUE,
                'label' => 'Swatch Value',
                'type'  => 'varchar'
            ],

            [
                'code'  => BrandImport::COL_ATTR_CODE,
                'label' => 'Attribute Code',
                'type'  => 'varchar'
            ]
        ];

        if (count($this->collection) === 0) {
            foreach ($listAttributes as $listAttribute) {
                $sourceCodeAttribute = $this->attributeFactory->create();
                $sourceCodeAttribute->setId($listAttribute['code']);
                $sourceCodeAttribute->setDefaultFrontendLabel($listAttribute['label']);
                $sourceCodeAttribute->setAttributeCode($listAttribute['code']);
                $sourceCodeAttribute->setBackendType($listAttribute['type']);
                if (isset($listAttribute['frontend_input']) && $listAttribute['frontend_input'] === 'select') {
                    $sourceCodeAttribute->setFrontendInput('select');
                    if ($listAttribute['code'] === BrandImport::COL_FEATURED) {
                        $sourceCodeAttribute->setSourceModel(EnableDisable::class);
                    } else {
                        $sourceCodeAttribute->setSourceModel(YesNo::class);
                    }
                }
                $this->collection->addItem($sourceCodeAttribute);
            }

        }

        return $this->collection;
    }
}
