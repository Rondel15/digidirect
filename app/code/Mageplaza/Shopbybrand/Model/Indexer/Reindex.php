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
 * @package   Mageplaza_Shopbybrand
 * @copyright Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license   https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Shopbybrand\Model\Indexer;

use Magento\Catalog\Model\Product;
use Magento\CatalogRule\Model\Indexer\AbstractIndexer;
use Magento\CatalogRule\Model\Indexer\IndexBuilder;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Config\Storage\Writer;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Mageplaza\Shopbybrand\Helper\Data;
use Mageplaza\Shopbybrand\Model\Reindex\Reindex as ModelReindex;
use Mageplaza\Shopbybrand\Model\ResourceModel\AbstractReport;
use Mageplaza\Shopbybrand\Model\ResourceModel\Grid\Report\BrandReport as BrandCreated;
use Mageplaza\Shopbybrand\Console\Command\Reindex as CommandReindex;
use Psr\Log\LoggerInterface;

/**
 * Class Template
 *
 * @package Mageplaza\Shopbybrand\Model\Indexer
 */
class Reindex extends AbstractIndexer
{
    const TYPE     = 'type';
    const DURATION = 'duration';

    /**
     * @var Writer
     */
    protected $_writer;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var CommandReindex|ModelReindex
     */
    protected $reindex;

    /**
     * @var BrandCreated
     */
    protected $brandCreated;

    /**
     * @var DateTime
     */
    protected $date;

    /**
     * @var TimezoneInterface
     */
    protected $_localeDate;

    /**
     * @var RequestInterface
     */
    protected $_request;

    /**
     * Reindex constructor.
     *
     * @param IndexBuilder $indexBuilder
     * @param ManagerInterface $eventManager
     * @param Writer $writer
     * @param RequestInterface $requestInterface
     * @param Data $helper
     * @param LoggerInterface $logger
     * @param ModelReindex $reindex
     * @param DateTime $date
     * @param TimezoneInterface $localeDate
     * @param BrandCreated $brandCreated
     */
    public function __construct(
        IndexBuilder $indexBuilder,
        ManagerInterface $eventManager,
        Writer $writer,
        RequestInterface $requestInterface,
        Data $helper,
        LoggerInterface $logger,
        ModelReindex $reindex,
        DateTime $date,
        TimezoneInterface $localeDate,
        BrandCreated $brandCreated
    ) {
        $this->_writer      = $writer;
        $this->_request     = $requestInterface;
        $this->helper       = $helper;
        $this->logger       = $logger;
        $this->reindex      = $reindex;
        $this->date         = $date;
        $this->_localeDate  = $localeDate;
        $this->brandCreated = $brandCreated;

        parent::__construct($indexBuilder, $eventManager);
    }

    /**
     * @return bool|void
     */
    public function executeFull()
    {
        //Default id of the brand reports is 10
        $data   = $this->reindex->load(10);
        $status = $this->reindexEach($data);
        if ($status) {
            $data->setUpdatedAt($this->date->date())->save();

            return true;
        }

        return false;
    }

    /**
     * @param Object $item
     * @param null $date
     *
     * @return bool
     */
    public function reindexEach($item, $date = null)
    {
        switch ($item->getCode()) {
            case 'mageplaza_reports_brand_aggregated':
                $this->reindexByCode($this->brandCreated, $date);
                $result = true;
                break;
            default:
                $result = false;
        }

        return $result;
    }

    /**
     * @param AbstractReport $createdAtModel
     * @param null $date
     */
    public function reindexByCode($createdAtModel, $date)
    {
        if ($date === null) {
            $this->aggregatePart($createdAtModel, $date);
        } else {
            $createdAtModel->aggregate($date);
        }
    }

    /**
     * @param AbstractReport $model
     * @param null $fromDate
     * @param null $toDate
     * @param null $range
     */
    public function aggregatePart($model, $fromDate = null, $toDate = null, $range = null)
    {
        $result = $model->aggregate($fromDate, $toDate, $range);
        if (isset($result['range']['first']) && $result['range']['first']) {
            $this->aggregatePart($model, $fromDate, $toDate, $result['range']);
        }
    }

    /**
     * @param int[] $ids
     *
     * @throws LocalizedException
     */
    protected function doExecuteList($ids)
    {
        $this->indexBuilder->reindexByIds(array_unique($ids));
        $this->getCacheContext()->registerEntities(Product::CACHE_TAG, $ids);
    }

    /**
     * {@inheritdoc}
     */
    protected function doExecuteRow($id)
    {
        $this->indexBuilder->reindexById($id);
    }
}
