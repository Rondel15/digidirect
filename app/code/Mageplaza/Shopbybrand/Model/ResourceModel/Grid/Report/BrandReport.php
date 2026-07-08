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

use Exception;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product\Type;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\DB\Select;
use Magento\GroupedProduct\Model\Product\Type\Grouped;
use Mageplaza\Shopbybrand\Model\ResourceModel\AbstractReport;
use Zend_Db_Expr;

/**
 * Class BrandReport
 * @package Mageplaza\Shopbybrand\Model\ResourceModel\Grid\Report
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class BrandReport extends AbstractReport
{
    /**
     * Initialize resource model
     *
     * @return void
     */

    protected function _construct()
    {
        $this->_init('mageplaza_brand_report', 'id');
    }

    /**
     * @param string $aggregationField
     * @param null $fromDate
     * @param null $toDate
     *
     * @param null $range
     *
     * @return array
     * @throws Exception
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _aggregate($aggregationField, $fromDate = null, $toDate = null, $range = null)
    {
        $checkRange = parent::_aggregate($aggregationField, $fromDate, $toDate, $range);
        $connection = $this->getConnection();
        $connection->beginTransaction();

        try {
            if ($checkRange) {
                $this->_clearTableByDateRange($this->getMainTable());
                $connection->commit();

                return $checkRange;
            }
            // convert dates to current admin timezone
            $createdAtExpr = $connection->getDatePartSql(
                $this->getStoreTZOffsetQuery(
                    ['source_table' => $this->getTable('sales_order_item')],
                    'source_table.created_at'
                )
            );
            $select        = $connection->select();
            $prdId         = $this->metadataPool->getMetadata(ProductInterface::class)->getLinkField();

            $columns = [
                'order_created_at' => $createdAtExpr,
                'store_id'         => new Zend_Db_Expr('source_table.store_id'),
                'status'           => new Zend_Db_Expr('so.status'),
                'order_id'         => new Zend_Db_Expr('source_table.order_id'),
                'qty_order'        => new Zend_Db_Expr('IF(so.status = "canceled" OR (IFNULL(source_table.qty_invoiced,0) - IFNULL(source_table.qty_refunded,0) - IFNULL(source_table.qty_canceled,0) = 0),0,1)'),
                'qty_item'         => new Zend_Db_Expr(
                    'IFNULL(source_table.qty_invoiced,0) -' .
                    'IFNULL(source_table.qty_refunded,0) -' .
                    'IFNULL(source_table.qty_canceled,0)'
                ),
                'row_total'        => new Zend_Db_Expr(
                    'IF(source_table.parent_item_id IS NULL ' .
                    'OR source_table.parent_item_id = 0,IFNULL(source_table.base_row_invoiced,0),' .
                    'IFNULL(order_item_parent.base_row_invoiced,0))' .
                    '- IF(source_table.parent_item_id IS NULL ' .
                    'OR source_table.parent_item_id = 0,IFNULL(IFNULL(source_table.base_discount_invoiced,0) - IFNULL(source_table.base_discount_refunded,0),0),' .
                    'IFNULL(IFNULL(order_item_parent.base_discount_invoiced,0) - IFNULL(order_item_parent.base_discount_refunded,0),0))' .
                    '- IF(source_table.parent_item_id IS NULL ' .
                    'OR source_table.parent_item_id = 0, IFNULL(source_table.base_amount_refunded,0),' .
                    'IFNULL(order_item_parent.base_amount_refunded,0))' .
                    '- IF(source_table.parent_item_id IS NULL ' .
                    'OR source_table.parent_item_id = 0,IF(source_table.qty_canceled = 0 OR source_table.qty_canceled IS NULL, 0, source_table.qty_canceled * source_table.base_price),' .
                    'IF(order_item_parent.qty_canceled = 0 OR order_item_parent.qty_canceled IS NULL, 0, order_item_parent.qty_canceled * order_item_parent.base_price))' .
                    '+ IF(source_table.parent_item_id IS NULL ' .
                    'OR source_table.parent_item_id = 0,IFNULL(IFNULL(source_table.base_tax_invoiced,0) - IFNULL(source_table.base_tax_refunded,0),0),' .
                    'IFNULL(IFNULL(order_item_parent.base_tax_invoiced,0) - IFNULL(order_item_parent.base_tax_refunded,0),0))'
                ),
                'discount'         => new Zend_Db_Expr(
                    'IF(source_table.parent_item_id IS NULL ' .
                    'OR source_table.parent_item_id = 0,IFNULL(IFNULL(source_table.base_discount_invoiced,0) - IFNULL(source_table.base_discount_refunded,0),0),' .
                    'IFNULL(IFNULL(order_item_parent.base_discount_invoiced,0) - IFNULL(order_item_parent.base_discount_refunded,0),0))'
                ),
                'tax'              => new Zend_Db_Expr(
                    'IF(source_table.parent_item_id IS NULL ' .
                    'OR source_table.parent_item_id = 0,IFNULL(IFNULL(source_table.base_tax_invoiced,0) - IFNULL(source_table.base_tax_refunded,0),0),' .
                    'IFNULL(IFNULL(order_item_parent.base_tax_invoiced,0) - IFNULL(order_item_parent.base_tax_refunded,0),0))'
                ),
                'refunded'         => new Zend_Db_Expr(
                    'IF(source_table.parent_item_id IS NULL ' .
                    'OR source_table.parent_item_id = 0, IFNULL(source_table.base_amount_refunded,0) + IFNULL(source_table.base_tax_refunded,0) - IFNULL(source_table.base_discount_refunded,0),' .
                    'IFNULL(order_item_parent.base_amount_refunded,0) + IFNULL(order_item_parent.base_tax_refunded,0) - IFNULL(order_item_parent.base_discount_refunded,0))'
                ),
                'canceled'         => new Zend_Db_Expr(
                    'IF(source_table.parent_item_id IS NULL ' .
                    'OR source_table.parent_item_id = 0,IF(source_table.qty_canceled = 0 OR source_table.qty_canceled IS NULL, 0, source_table.qty_canceled * source_table.base_price + IFNULL(source_table.base_tax_invoiced,0) - IFNULL(source_table.base_discount_invoiced,0)),' .
                    'IF(order_item_parent.qty_canceled = 0 OR order_item_parent.qty_canceled IS NULL, 0, order_item_parent.qty_canceled * order_item_parent.base_price + IFNULL(order_item_parent.base_tax_invoiced,0) - IFNULL(order_item_parent.base_discount_invoiced,0)))'
                ),
                'item_id'          => new Zend_Db_Expr('source_table.item_id'),
                'product_id'       => new Zend_Db_Expr('source_table.product_id'),
                'product_name'     => new Zend_Db_Expr('source_table.name'),
                'sku'              => new Zend_Db_Expr('source_table.sku'),
                'attribute_id'     => new Zend_Db_Expr('ea.attribute_id'),
                'attribute_code'   => new Zend_Db_Expr('ea.attribute_code'),
                'attribute_value'  => new Zend_Db_Expr('att.value'),
                'attribute_name'   => new Zend_Db_Expr('eat.value'),
            ];

            $attrBrandCode = $this->helperData->getAttributeCode($this->_store->getStore()->getId());
            $attrBrandId   = $this->helperData->getAttributeId($attrBrandCode);
            $attrType      = $this->getAttributeType($attrBrandCode);

            $attributeTable[] = $connection->select()
                ->from(
                    ['cpedt' => $this->getTable('catalog_product_entity_datetime')],
                    ['attribute_id' => 'cpedt.attribute_id', 'value' => 'cpedt.value', 'product_id' => "cpedt.{$prdId}"]
                )->where('cpedt.attribute_id = ?', $attrBrandId);
            $attributeTable[] = $connection->select()
                ->from(
                    ['cped' => $this->getTable('catalog_product_entity_decimal')],
                    ['attribute_id' => 'cped.attribute_id', 'value' => 'cped.value', 'product_id' => "cped.{$prdId}"]
                )->where('cped.attribute_id = ?', $attrBrandId);
            $attributeTable[] = $connection->select()
                ->from(
                    ['cpei' => $this->getTable('catalog_product_entity_int')],
                    ['attribute_id' => 'cpei.attribute_id', 'value' => 'cpei.value', 'product_id' => "cpei.{$prdId}"]
                )->where('cpei.attribute_id = ?', $attrBrandId);
            $attributeTable[] = $connection->select()
                ->from(
                    ['cpet' => $this->getTable('catalog_product_entity_text')],
                    ['attribute_id' => 'cpet.attribute_id', 'value' => 'cpet.value', 'product_id' => "cpet.{$prdId}"]
                )->where('cpet.attribute_id = ?', $attrBrandId);
            $attributeTable[] = $connection->select()
                ->from(
                    ['cpev' => $this->getTable('catalog_product_entity_varchar')],
                    ['attribute_id' => 'cpev.attribute_id', 'value' => 'cpev.value', 'product_id' => "cpev.{$prdId}"]
                )->where('cpev.attribute_id = ?', $attrBrandId);
            $attributeTable[] = $connection->select()
                ->from(
                    ['cpie' => $this->getTable('catalog_product_index_eav_idx')],
                    ['attribute_id' => 'cpie.attribute_id', 'value' => 'cpie.value', 'product_id' => 'cpie.entity_id']
                )->where('cpie.attribute_id = ?', $attrBrandId);

            $selectAttr = $connection->select()->union($attributeTable, Select::SQL_UNION_ALL);

            $select->from(
                ['source_table' => $this->getTable('sales_order_item')],
                $columns
            )->joinInner(
                ['so' => $this->getTable('sales_order')],
                'so.entity_id = source_table.order_id',
                []
            )->joinInner(
                ['att' => $selectAttr],
                'source_table.product_id = att.product_id',
                []
            )->joinLeft(
                ['ea' => $this->getTable('eav_attribute')],
                'ea.attribute_id = att.attribute_id',
                []
            )->joinLeft(
                ['order_item_parent' => $this->getTable('sales_order_item')],
                'source_table.parent_item_id = order_item_parent.item_id',
                []
            );

            if ($attrType === 'select') {
                $select->joinLeft(
                    ['eat' => $this->getTable('eav_attribute_option_value')],
                    'eat.option_id = att.value AND eat.store_id = 0',
                    []
                );
            } else {
                $select->joinLeft(
                    ['eat' => $this->getTable('eav_attribute_option_swatch')],
                    'eat.option_id = att.value AND eat.store_id = 0',
                    []
                );
            }

            $select->where(
                'source_table.product_type NOT IN(?)',
                [
                    Type::TYPE_BUNDLE       => Type::TYPE_BUNDLE,
                    Grouped::TYPE_CODE      => Grouped::TYPE_CODE,
                ]
            );

            $select->useStraightJoin();
            $insertQuery = $select->insertFromSelect($this->getMainTable(), array_keys($columns));
            $connection->query($insertQuery);
            $connection->commit();
        } catch (Exception $e) {
            $connection->rollBack();
            throw $e;
        }
    }
}
