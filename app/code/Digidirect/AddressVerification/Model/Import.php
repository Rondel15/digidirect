<?php
namespace Digidirect\AddressVerification\Model;

use Digidirect\AddressVerification\Helper\Autocomplete;
use Digidirect\AddressVerification\Model\Config\Source\Type;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Import
 * @package Digidirect\AddressVerification\Model
 */
class Import extends DataObject
{
    /**
     * @var Import\ParserFactory
     */
    protected $parserFactory;

    /**
     * @var \Digidirect\AddressVerification\Api\LocationRepositoryInterface
     */
    protected $locationRepository;

    /**
     * @var \Digidirect\AddressVerification\Api\ImportReportRepositoryInterface
     */
    protected $reportRepository;

    /**
     * @var \Digidirect\AddressVerification\Helper\Autocomplete
     */
    protected $helper;

    /**
     * @var null|\Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    /**
     * @var \Magento\Config\Model\ResourceModel\Config
     */
    protected $resourceConfig;

    /**
     * Import constructor.
     * @param Import\ParserFactory $parserFactory
     * @param \Digidirect\AddressVerification\Api\LocationRepositoryInterface $locationRepository
     * @param \Digidirect\AddressVerification\Api\ImportReportRepositoryInterface $reportRepository
     * @param \Digidirect\AddressVerification\Helper\Autocomplete $helper
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Config\Model\ResourceModel\Config $resourceConfig
     * @param array $data
     */
    public function __construct(
        \Digidirect\AddressVerification\Model\Import\ParserFactory $parserFactory,
        \Digidirect\AddressVerification\Api\LocationRepositoryInterface $locationRepository,
        \Digidirect\AddressVerification\Api\ImportReportRepositoryInterface $reportRepository,
        \Digidirect\AddressVerification\Helper\Autocomplete $helper,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Config\Model\ResourceModel\Config $resourceConfig,
        $data = []
    ) {
        parent::__construct($data);
        $this->parserFactory = $parserFactory;
        $this->locationRepository = $locationRepository;
        $this->reportRepository = $reportRepository;
        $this->helper = $helper;
        $this->logger = $logger;
        $this->date = $date;
        $this->resourceConfig = $resourceConfig;
    }

    /**
     * @param string $file
     * @param string $countryCode
     * @param null|int $storeId
     * @param null|int $websiteId
     * @return $this
     * @throws \Exception
     */
    public function processImport($file, $countryCode, $storeId = null, $websiteId = null)
    {
        try {
            $adapter = $this->parserFactory->create($this->extractParserType($file));
            $adapter->setStoreId($storeId);
            $adapter->setWebsiteId($websiteId);
            $adapter->setCountryCode($countryCode);
            $adapter->setFile($file);
            $data = $adapter->parse();
            $this->locationRepository->saveData($data, $countryCode, $storeId, $websiteId);
            $this->reportRepository->updateReport(
                $countryCode,
                Type::AU_POST,
                $this->getLastImportTime(),
                $storeId,
                $websiteId
            );
            $this->dropFile($storeId, $websiteId);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw $e;
        }
        return $this;
    }

    /**
     * @param string $storeId
     * @param string $websiteId
     * @return void
     */
    protected function dropFile($storeId, $websiteId)
    {
        $scopeInfo = $this->helper->getScopeInfo($storeId, $websiteId);
        $this->resourceConfig->saveConfig(
            Autocomplete::DIGIDIRECT_ADDRESS_VERIFICATION_GENERAL_CONFIG_PATH . '/address_data_file',
            null,
            $scopeInfo->getScopeType(),
            $scopeInfo->getScopeId()
        );
    }

    /**
     * @return string
     */
    public function getLastImportTime()
    {
        if (!$this->hasData('last_import_time')) {
            $this->setData('last_import_time', $this->date->gmtDate());
        }
        return $this->getData('last_import_time');
    }
    
    /**
     * @param string $file
     * @return mixed
     */
    protected function extractParserType($file)
    {
        return pathinfo($file, PATHINFO_EXTENSION);
    }
}
