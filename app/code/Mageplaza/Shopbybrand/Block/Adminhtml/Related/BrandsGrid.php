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

namespace Mageplaza\Shopbybrand\Block\Adminhtml\Related;

use Exception;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Grid;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Helper\Data;
use Magento\Framework\Exception\FileSystemException;
use Mageplaza\Shopbybrand\Block\Adminhtml\Related\Edit\Tab\Renderer\Store;
use Mageplaza\Shopbybrand\Helper\Data as BrandHelper;

/**
 * Class BrandsGrid
 * @package Mageplaza\Shopbybrand\Block\Adminhtml\Related
 */
class BrandsGrid extends Extended
{

    /**
     * @var BrandHelper
     */
    protected $helper;

    /**
     * @var int
     */
    protected $optionId;

    /**
     * @var array
     */
    protected $relatedBrands;

    /**
     * @param Context $context
     * @param Data $backendHelper
     * @param BrandHelper $helper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $backendHelper,
        BrandHelper $helper,
        array $data = []
    ) {
        $this->helper = $helper;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @throws FileSystemException
     */
    protected function _construct()
    {
        parent::_construct();

        $this->setId('mpbrand-grid-related');
        $this->setDefaultSort('default_value');
        $this->setDefaultDir('ASC');
        $this->setUseAjax(true);
    }

    /**
     * @return Grid
     */
    protected function _prepareCollection()
    {
        $collection = $this->helper()->getBrandList(null, null, $this->_request->getParam('store_id'));
        $collection->addFieldToFilter('main_table.option_id', ['neq' => $this->optionId]);
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * @return Extended
     * @throws Exception
     */
    protected function _prepareColumns()
    {
        $this->addColumn('index', [
            'type'             => 'checkbox',
            'index'            => 'option_id',
            'header_css_class' => 'col-select',
            'column_css_class' => 'col-select',
            'align'            => 'center',
            'filter'           => false,
        ]);
        $this->addColumn('default_value', [
            'header'   => __('Brand Name'),
            'index'    => 'default_value',
            'sortable' => true,
        ]);

        $this->addColumn('option_id_sort', [
            'header'   => __('Option ID'),
            'index'    => 'option_id',
            'type'     => 'number',
            'sortable' => true,
        ]);

        $this->addColumn('store_id', [
            'header'           => __('Store view'),
            'index'            => 'store_id',
            'sortable'         => false,
            'filter'           => false,
            'column_css_class' => 'admin__scope-old',
            'renderer'         => Store::class,
        ]);

        return parent::_prepareColumns();
    }

    /**
     * Sets sorting order by some column
     *
     * @param \Magento\Backend\Block\Widget\Grid\Column $column
     *
     * @return $this
     */
    protected function _setCollectionOrder($column)
    {
        $collection = $this->getCollection();
        if ($collection) {
            $columnIndex = $column->getFilterIndex() ? $column->getFilterIndex() : $column->getIndex();
            $collection->getSelect()->reset(\Zend_Db_Select::ORDER);
            if ($columnIndex === 'option_id') {
                $collection->getSelect()->order(new \Zend_Db_Expr('tdv.option_id ' . strtoupper($column->getDir())));
            } elseif ($columnIndex === 'default_value') {
                $collection->getSelect()->order(new \Zend_Db_Expr('default_value ' . strtoupper($column->getDir())));
            }
        }

        return $this;
    }

    /**
     * @param Grid\Column $column
     *
     * @return $this|BrandsGrid
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _addColumnFilterToCollection($column)
    {
        if ($this->getCollection()) {
            $field     = $column->getFilterIndex() ? $column->getFilterIndex() : $column->getIndex();
            $condition = $column->getFilter()->getCondition();
            if ($field === 'default_value') {
                $field = 'tdv.value';
            } elseif ($field === 'option_id') {
                $field = 'tdv.option_id';
            }
            if ($field && isset($condition)) {
                $this->getCollection()->addFieldToFilter($field, $condition);
            }
        }

        return $this;
    }

    /**
     * @return BrandHelper
     */
    public function helper()
    {
        return $this->helper;
    }

    /**
     * @param int $optionId
     *
     * @return $this
     */
    public function setOptionId($optionId)
    {
        $this->optionId = $optionId;

        return $this;
    }
}
