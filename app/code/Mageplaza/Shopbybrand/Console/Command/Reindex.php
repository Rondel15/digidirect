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

namespace Mageplaza\Shopbybrand\Console\Command;

use Exception;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Config\Storage\Writer;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Mageplaza\Shopbybrand\Helper\Data;
use Mageplaza\Shopbybrand\Model\Reindex\Reindex as ModelReindex;
use Mageplaza\Shopbybrand\Model\ResourceModel\AbstractReport;
use Mageplaza\Shopbybrand\Model\ResourceModel\Grid\Report\BrandReport as BrandCreated;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Reindex
 * @package Mageplaza\Shopbybrand\Console\Command
 */
class Reindex extends Command
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
     * @var Reindex
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
     * @param Writer $writer
     * @param RequestInterface $requestInterface
     * @param Data $helper
     * @param LoggerInterface $logger
     * @param ModelReindex $reindex
     * @param DateTime $date
     * @param TimezoneInterface $localeDate
     * @param BrandCreated $brandCreated
     * @param null $name
     */
    public function __construct(
        Writer $writer,
        RequestInterface $requestInterface,
        Data $helper,
        LoggerInterface $logger,
        ModelReindex $reindex,
        DateTime $date,
        TimezoneInterface $localeDate,
        BrandCreated $brandCreated,
        $name = null
    ) {
        $this->_writer      = $writer;
        $this->_request     = $requestInterface;
        $this->helper       = $helper;
        $this->logger       = $logger;
        $this->reindex      = $reindex;
        $this->date         = $date;
        $this->_localeDate  = $localeDate;
        $this->brandCreated = $brandCreated;

        parent::__construct($name);
    }

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $options = [
            new InputOption(
                self::TYPE,
                null,
                InputOption::VALUE_REQUIRED,
                'type'
            ),
            new InputOption(
                self::DURATION,
                null,
                InputArgument::OPTIONAL,
                'time interval'
            ),
            new InputArgument(
                'ids',
                InputArgument::OPTIONAL | InputArgument::IS_ARRAY,
                'Space-separated list of index types or omit to apply to all indexes.'
            )

        ];

        $this->setName('mpbrand:reindex')
            ->setDescription('Reindex via command line')
            ->setDefinition($options);

        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>Running...</info>');
        try {
            //Default id of the brand reports is 10
            $data   = $this->reindex->load(10);
            $status = $this->reindexEach($data);
            if ($status) {
                $data->setUpdatedAt($this->date->date())->save();
                $output->writeln(
                    '<info>The brand reports have been reindexed successfully!</info>'
                );
            }
        } catch (Exception $e) {
            $output->writeln("<error>{$e->getMessage()}</error>");
        }

        return true;
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
}
