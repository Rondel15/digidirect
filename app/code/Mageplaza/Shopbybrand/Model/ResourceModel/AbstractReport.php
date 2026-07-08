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

namespace Mageplaza\Shopbybrand\Model\ResourceModel;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\Timezone\Validator;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Reports\Model\FlagFactory;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\Shopbybrand\Helper\Data;
use Psr\Log\LoggerInterface;

/**
 * Class AbstractReport
 * @package Mageplaza\Shopbybrand\Model\ResourceModel
 */
abstract class AbstractReport extends \Magento\Reports\Model\ResourceModel\Report\AbstractReport
{
    /**
     * @var string
     */
    protected $indexField = 'brand_report';

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var MetadataPool
     */
    protected $metadataPool;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var StoreManagerInterface
     */
    protected $_store;

    /**
     * @var CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * AbstractReport constructor.
     *
     * @param Context $context
     * @param LoggerInterface $logger
     * @param TimezoneInterface $localeDate
     * @param FlagFactory $reportsFlagFactory
     * @param Validator $timezoneValidator
     * @param DateTime $dateTime
     * @param StoreManagerInterface $storeManager
     * @param CollectionFactory $collectionFactory
     * @param Data $helperData
     * @param RequestInterface $request
     * @param MetadataPool $metadataPool
     * @param null $connectionName
     */
    public function __construct(
        Context $context,
        LoggerInterface $logger,
        TimezoneInterface $localeDate,
        FlagFactory $reportsFlagFactory,
        Validator $timezoneValidator,
        DateTime $dateTime,
        StoreManagerInterface $storeManager,
        CollectionFactory $collectionFactory,
        Data $helperData,
        RequestInterface $request,
        MetadataPool $metadataPool,
        $connectionName = null
    ) {
        $this->request            = $request;
        $this->_store             = $storeManager;
        $this->_collectionFactory = $collectionFactory;
        $this->metadataPool       = $metadataPool;
        $this->helperData         = $helperData;

        parent::__construct(
            $context,
            $logger,
            $localeDate,
            $reportsFlagFactory,
            $timezoneValidator,
            $dateTime,
            $connectionName
        );
    }

    /**
     * @param string $table
     * @param string $field
     * @param int $min
     *
     * @return mixed
     */
    public function getRowId($table, $field, $min = 0)
    {
        $connection = $this->getConnection();
        $function   = $min ? 'MIN' : 'MAX';
        $query      = $connection->select()->from($table, $function . '(' . $field . ')');
        $result     = $connection->fetchCol($query);

        return array_shift($result);
    }

    /**
     * @param $aggregationField
     * @param null $fromDate
     * @param null $toDate
     * @param null $range
     *
     * @return array|null
     */
    protected function _aggregate($aggregationField, $fromDate = null, $toDate = null, $range = null)
    {
        $this->_clearTableByDateRange($this->getTable('mageplaza_brand_report'));
        if (empty($range) && $fromDate === null) {
            $minId = $this->getRowId($this->getTable('sales_order_item'), 'order_id', 1);
            $maxId = $this->getRowId($this->getTable('sales_order_item'), 'order_id');
            $range = [
                'first' => $minId,
                'last'  => $minId + 1000,
                'max'   => $maxId
            ];

            return [
                'id'    => $this->request->getParam('id'),
                'range' => $range,
                'error' => false
            ];
        }

        return null;
    }

    /**
     * @param null $fromDate
     * @param null $toDate
     *
     * @param null $range
     *
     * @return mixed
     */
    public function aggregate($fromDate = null, $toDate = null, $range = null)
    {
        return $this->_aggregate($this->indexField, $fromDate, $toDate, $range);
    }

    /**
     * @param string $attrCode
     *
     * @return string
     */
    protected function getAttributeType($attrCode)
    {
        $attributes = $this->_collectionFactory->create()->addVisibleFilter();
        $attrType   = '';

        foreach ($attributes as $attribute) {
            if ($attribute->getAttributeCode() === $attrCode) {
                $attrType = $attribute->getData('frontend_input');
            }
        }

        return $attrType;
    }
}
