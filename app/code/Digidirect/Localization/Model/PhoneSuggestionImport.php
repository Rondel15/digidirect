<?php

namespace Digidirect\Localization\Model;

use Digidirect\Localization\Model\Import\ParserTypeFactory;
use Magento\Framework\App\RequestInterface;

class PhoneSuggestionImport
{
    /**
     * System configuration file direcory
     */
    const DIRECTORY = 'localization';

    /**
     * Js plugin placeholder
     */
    const PLAGIN_PLACEHOLDER = '9';

    /**
     * @var array
     */
    protected $importFields = [
        Configuration::COUTNRY,
        Configuration::MASK_PATTERN,
        Configuration::MASK_PREFIX,
        Configuration::MASK_PLACEHOLDER,
    ];

    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    protected $directoryList;

    /**
     * @var ParserTypeFactory
     */
    protected $parserFactory;

    /**
     * @var \Magento\Directory\Model\ResourceModel\Country\Collection
     */
    protected $countryCollection;

    /**
     * @var \Magento\Config\Model\ResourceModel\Config
     */
    protected $configResource;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    protected $serializer;

    /**
     * PhoneSuggestionImport constructor.
     * @param ParserTypeFactory $parserFactory
     * @param \Magento\Framework\App\Filesystem\DirectoryList $directoryList
     * @param \Magento\Directory\Model\ResourceModel\Country\Collection $countryCollection
     * @param \Magento\Config\Model\ResourceModel\Config $configResource
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     */
    public function __construct(
        ParserTypeFactory $parserFactory,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Directory\Model\ResourceModel\Country\Collection $countryCollection,
        \Magento\Config\Model\ResourceModel\Config $configResource,
        \Magento\Framework\Serialize\SerializerInterface $serializer
    ) {
        $this->directoryList = $directoryList;
        $this->parserFactory = $parserFactory;
        $this->countryCollection = $countryCollection;
        $this->configResource = $configResource;
        $this->serializer = $serializer;
    }

    /**
     * @param string $file
     * @param string $scope
     * @param int $scopeId
     * @return bool|string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function import($file, $scope, $scopeId)
    {
        try {
            $parser = $this->parserFactory->create($this->extractProcessorType($file));
            $items = $parser->execute($this->getFilePath($file));
            $itemsToImport = $this->processItems($items);
            if (!empty($itemsToImport)) {
                $this->configResource->saveConfig(
                    Configuration::XML_PATH_PHONE_CODES,
                    $this->serializer->serialize($itemsToImport),
                    $scope,
                    $scopeId
                );
                $result = true;
            } else {
                $result = 'Nothing to import';
            }
        } catch (\Magento\Framework\Exception\FileSystemException $e) {
            $result = $e->getMessage();
        }

        return $result;
    }

    /**
     * @param array $items
     * @return array
     */
    protected function processItems(array $items)
    {
        $reuslt = [];
        $countries = $this->getCountriesByName();
        $iterator = 1;
        foreach ($items as $item) {
            if ($this->isItemValid($countries, $item)) {
                $key = '_' . time() . '_' . $iterator;
                $reuslt[$key] = [
                    Configuration::COUTNRY => $countries[$item[Configuration::COUTNRY]],
                    Configuration::MASK_PREFIX => $item[Configuration::MASK_PREFIX],
                    Configuration::MASK_PATTERN => $this->preparePattern($item[Configuration::MASK_PATTERN]),
                    Configuration::MASK_PLACEHOLDER => $this->preparePlacehloder(
                        $item[Configuration::MASK_PATTERN],
                        $item[Configuration::MASK_PLACEHOLDER]
                    ),
                ];
                $iterator++;
            }
        }
        return $reuslt;
    }

    /**
     * @param array $countries
     * @param array $item
     * @return bool
     */
    protected function isItemValid(array $countries, array $item)
    {
        return isset($countries[$item[Configuration::COUTNRY]])
            && empty(array_diff($this->importFields, array_keys($item)));
    }

    /**
     * @param string $pattern
     * @return string
     */
    protected function preparePattern($pattern)
    {
        $result = '';
        if (!empty($pattern)) {
            $result = str_pad(self::PLAGIN_PLACEHOLDER, (int)$pattern, self::PLAGIN_PLACEHOLDER);
        }
        return $result;
    }

    /**
     * @param int $pattern
     * @param string $placeholder
     * @return string
     */
    protected function preparePlacehloder($pattern, $placeholder)
    {
        $result = '';
        if (!empty($pattern) && !empty($placeholder)) {
            $result = str_pad($placeholder, (int)$pattern, $placeholder);
        }
        return $result;
    }

    /**
     * @param string $file
     * @return mixed
     */
    protected function extractProcessorType($file)
    {
        return pathinfo($file, PATHINFO_EXTENSION);
    }

    /**
     * @param string $file
     * @return string
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    protected function getFilePath($file)
    {
        return $this->directoryList->getPath('media')
            .  DIRECTORY_SEPARATOR  . self::DIRECTORY . DIRECTORY_SEPARATOR . $file;
    }

    /**
     * @return array
     */
    protected function getCountriesByName()
    {
        foreach ($this->countryCollection->toOptionArray() as $country) {
            $countiresByName[$country['label']] = $country['value'];
        }

        return $countiresByName;
    }
}
