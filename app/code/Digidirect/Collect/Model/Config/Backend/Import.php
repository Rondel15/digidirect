<?php

namespace Digidirect\Collect\Model\Config\Backend;

use Magento\Framework\DataObject;
use Digidirect\Collect\Model\ResourceModel\PostCode as PostCodeResources;
use Digidirect\Collect\Helper\Config\Import as ImportHelper;
use Digidirect\Collect\Model\Config\Backend\Parser as Parser;

/**
 * Class Import
 * @package Digidirect\Collect\Model
 */
class Import extends DataObject
{
    /**
     * @var null|\Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Config\Model\ResourceModel\Config
     */
    protected $resourceConfig;

    /**
     * @var Parser $parser
     */
    protected $parser;

    /**
     * @var PostCodeResources $PostCodeResources
     */
    protected $postCodeResources;

    /**
     * @var ImportHelper $helper
     */
    protected $helper;

    /**
     * Import constructor.
     * @param Parser $parser
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Config\Model\ResourceModel\Config $resourceConfig
     * @param PostCodeResources $postCodeResources
     * @param ImportHelper $importHelper
     * @param array $data
     */
    public function __construct(
        Parser $parser,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Config\Model\ResourceModel\Config $resourceConfig,
        PostCodeResources $postCodeResources,
        ImportHelper $importHelper,
        $data = []
    ) {
        parent::__construct($data);
        $this->logger = $logger;
        $this->resourceConfig = $resourceConfig;
        $this->parser = $parser;
        $this->postCodeResources = $postCodeResources;
        $this->helper = $importHelper;
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
            $adapter = $this->parser;
            $adapter->setStoreId($storeId);
            $adapter->setWebsiteId($websiteId);
            $adapter->setCountryCode($countryCode);
            $adapter->setFile($file);
            $data = $adapter->parse();
            $this->postCodeResources->saveData($data);
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
     * @throws \Exception
     */
    protected function dropFile($storeId, $websiteId)
    {
        $scopeInfo = $this->helper->getScopeInfo($storeId, $websiteId);
        $this->resourceConfig->saveConfig(
            ImportHelper::XML_POST_CODE_PATH,
            null,
            $scopeInfo->getScopeType(),
            $scopeInfo->getScopeId()
        );
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
