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
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Collection;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use Magento\ImportExport\Model\Export\Factory as ExportFactory;
use Magento\ImportExport\Model\Export\AbstractEntity;
use Magento\InventoryImportExport\Model\Export\ColumnProviderInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\ImportExport\Model\ResourceModel\CollectionByPagesIteratorFactory;
use Mageplaza\Shopbybrand\Helper\Data as BrandHelper;
use Mageplaza\Shopbybrand\Model\BrandFactory;
use Mageplaza\Shopbybrand\Model\Import\Brand as BrandImport;
use Magento\Store\Api\StoreRepositoryInterface;

/**
 * Class Brand
 * @package Mageplaza\Shopbybrand\Model\Export
 */
class Brand extends AbstractEntity
{
    /**
     * @var AttributeCollectionProvider
     */
    private $attributeCollectionProvider;

    /**
     * @var ColumnProviderInterface
     */
    private $columnProvider;

    /**
     * @var BrandHelper
     */
    protected $helper;

    /**
     * @var StoreRepositoryInterface
     */
    protected $storeRepository;

    /**
     * @var BrandFactory
     */
    protected $brandFactory;

    /**
     * @var array
     */
    protected $storeIdAttributeCodes;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param ExportFactory $collectionFactory
     * @param CollectionByPagesIteratorFactory $resourceColFactory
     * @param AttributeCollectionProvider $attributeCollectionProvider
     * @param ColumnProviderInterface $columnProvider
     * @param BrandHelper $helper
     * @param StoreRepositoryInterface $storeRepository
     * @param BrandFactory $brandFactory
     * @param array $data
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        ExportFactory $collectionFactory,
        CollectionByPagesIteratorFactory $resourceColFactory,
        AttributeCollectionProvider $attributeCollectionProvider,
        ColumnProviderInterface $columnProvider,
        BrandHelper $helper,
        StoreRepositoryInterface $storeRepository,
        BrandFactory $brandFactory,
        array $data = []
    ) {
        $this->attributeCollectionProvider = $attributeCollectionProvider;
        $this->columnProvider              = $columnProvider;
        $this->helper                      = $helper;
        $this->storeRepository             = $storeRepository;
        $this->brandFactory                = $brandFactory;

        parent::__construct($scopeConfig, $storeManager, $collectionFactory, $resourceColFactory, $data);
    }

    /**
     * @return Collection
     * @throws Exception
     */
    public function getAttributeCollection()
    {
        return $this->attributeCollectionProvider->get();
    }

    /**
     * @return string
     * @throws LocalizedException
     * @throws Exception
     */
    public function export()
    {
        $brandFactory = $this->brandFactory->create();
        $writer       = $this->getWriter();
        $writer->setHeaderCols($this->_getHeaderColumns());

        if (!$this->isSingleStoreMode()) {
            foreach ($this->storeRepository->getList() as $store) {
                $brandData = $brandFactory->getBrandCollection($store->getId())->getData();
                $brandData = $this->initBrandData($brandData, $store->getId());
                $brandData = $this->filterData($brandData);

                foreach ($brandData as $data) {
                    $writer->writeRow($data);
                }
            }
        } else {
            $brandData = $brandFactory->getBrandCollection()->getData();
            $brandData = $this->initBrandData($brandData, $this->_storeManager->getDefaultStoreView()->getId());

            foreach ($brandData as $data) {
                $writer->writeRow($data);
            }
        }

        return $writer->getContents();
    }

    /**
     * @param array $brandData
     * @param int $storeId
     *
     * @return array
     */
    public function initBrandData($brandData, $storeId)
    {
        $this->storeIdAttributeCodes[] = [
            BrandImport::COL_STORE_ID  => $storeId,
            BrandImport::COL_ATTR_CODE => $this->helper->getAttributeCode($storeId)
        ];
        foreach ($brandData as $key => $data) {
            $brandData[$key][BrandImport::COL_NAME]      = $data['default_value'];
            $brandData[$key][BrandImport::COL_VALUE]     = $data['value'];
            $brandData[$key][BrandImport::COL_ATTR_CODE] = $this->getAttributeCode($data['store_id']);
            if (!$data[AttributeCollectionProvider::COL_DISPLAY]) {
                $brandData[$key][AttributeCollectionProvider::COL_DISPLAY] = 1;//make default is 1
            }
            if (!$data[BrandImport::COL_FEATURED]) {
                $brandData[$key][BrandImport::COL_FEATURED] = 0;//make default is 0
            }
        }

        return $brandData;
    }

    /**
     * get Attribute Code of brand from  $storeIdAttributeCodes
     *
     * @param int $storeId
     *
     * @return string
     */
    public function getAttributeCode($storeId)
    {
        foreach ($this->storeIdAttributeCodes as $storeIdAttributeCode) {
            if (isset($storeIdAttributeCode[BrandImport::COL_STORE_ID]) &&
                $storeIdAttributeCode[BrandImport::COL_STORE_ID] === $storeId) {

                return $storeIdAttributeCode[BrandImport::COL_ATTR_CODE];
            }
        }

        return '';
    }

    /**
     * check is single store
     *
     * @return bool
     */
    public function isSingleStoreMode()
    {
        return $this->_storeManager->isSingleStoreMode();
    }

    /**
     * @return array
     * @throws Exception
     */
    protected function _getHeaderColumns()
    {
        return $this->columnProvider->getHeaders($this->getAttributeCollection(), $this->_parameters);
    }

    /**
     * @param AbstractModel $item
     */
    public function exportItem($item)
    {
        // not use this method
    }

    /**
     * @return string
     */
    public function getEntityTypeCode()
    {
        return 'mp_brand';
    }

    /**
     * @return void
     */
    protected function _getEntityCollection()
    {
        // not use this method
    }

    /**
     * @param $data
     *
     * @return array
     * @throws Exception
     */
    public function filterData($data)
    {
        $data      = $this->skipAttribute($data);
        $brandData = [];
        foreach ($data as $item) {
            if ($this->checkCondition($item)) {
                $brandData[] = $item;
            }

        }

        return $brandData;
    }

    /**
     * @param $data
     *
     * @return mixed
     */
    public function skipAttribute($data)
    {
        if (isset($this->_parameters['skip_attr'])) {
            foreach ($this->_parameters['skip_attr'] as $skipAttribute) {
                if (isset($data[$skipAttribute])) {
                    foreach ($data as $item) {
                        unset($item[$skipAttribute]);
                    }
                }
            }
        }

        return $data;
    }

    /**
     * @param $item
     *
     * @return bool
     * @throws Exception
     */
    public function checkCondition($item)
    {
        foreach ($this->_parameters['export_filter'] as $filterName => $filter) {
            if ($filter !== "") {
                if ($this->getAttributeCollection()->getItemById($filterName)->getData('backend_type') === 'int'
                    && is_array($filter) && (!empty($filter[0]) || !empty($filter[1]))) {
                    if (!empty($filter[0])) {
                        if ($item[$filterName] < $filter[0]) {
                            return false;
                        }
                    } elseif (!empty($filter[1])) {
                        if ($item[$filterName] > $filter[1]) {
                            return false;
                        }
                    }
                }
                if (is_string($filter)) {
                    if ($item[$filterName] != $filter) {
                        return false;
                    }
                }

            }

        }

        return true;
    }
}
