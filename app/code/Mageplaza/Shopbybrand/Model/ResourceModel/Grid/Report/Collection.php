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

namespace Mageplaza\Shopbybrand\Model\ResourceModel\Grid\Report;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;
use Mageplaza\Shopbybrand\Helper\Data;
use Psr\Log\LoggerInterface as Logger;
use Zend_Db_Expr;

/**
 * Class Collection
 * @package Mageplaza\Shopbybrand\Model\ResourceModel\Grid\Report
 */
class Collection extends SearchResult
{
    /**
     * @var RequestInterface
     */
    protected $_request;

    /**
     * @var array
     */
    protected $_selectedColumns = [];

    /**
     * @var Data
     */
    protected $_helperData;

    /**
     * @var null
     */
    protected $categoryId;

    /**
     * Collection constructor.
     *
     * @param EntityFactory $entityFactory
     * @param Logger $logger
     * @param FetchStrategy $fetchStrategy
     * @param EventManager $eventManager
     * @param RequestInterface $request
     * @param Data $helperData
     * @param string $mainTable
     * @param string $resourceModel
     *
     * @throws LocalizedException
     */
    public function __construct(
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        RequestInterface $request,
        Data $helperData,
        $mainTable = 'mageplaza_brand_report',
        $resourceModel = BrandReport::class
    ) {

        $this->_request    = $request;
        $this->_helperData = $helperData;

        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $mainTable,
            $resourceModel
        );
    }

    /**
     * Init collection select
     *
     * @return $this
     */
    protected function _initSelect()
    {
        $tableName = $this->getMainTable();
        $this->getSelect()->from(['main_table' => $tableName], $this->_getSelectedColumns());

        return $this;
    }

    /**
     * Retrieve selected columns
     *
     * @return array
     */
    protected function _getSelectedColumns()
    {
        $this->_selectedColumns = [
            'order_created_at' => new Zend_Db_Expr('order_created_at'),
            'attribute_name'   => new Zend_Db_Expr('attribute_name'),
            'attribute_id'     => new Zend_Db_Expr('attribute_id'),
            'attribute_code'   => new Zend_Db_Expr('attribute_code'),
            'qty_ordered'      => new Zend_Db_Expr('SUM(qty_order)'),
            'qty_item'         => new Zend_Db_Expr('SUM(qty_item)'),
            'total'            => new Zend_Db_Expr('SUM(row_total)'),
            'discount'         => new Zend_Db_Expr('SUM(discount)'),
            'tax'              => new Zend_Db_Expr('SUM(tax)'),
            'refunded'         => new Zend_Db_Expr('SUM(refunded)'),
        ];

        $this->getSelect()->group(['attribute_name']);

        return $this->_selectedColumns;
    }
}
