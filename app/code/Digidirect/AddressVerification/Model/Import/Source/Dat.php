<?php

namespace Digidirect\AddressVerification\Model\Import\Source;

use Digidirect\AddressVerification\Model\Import\AbstractSource;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * Class Parser
 *
 * @package Digidirect\AddressVerification\Model\Import\Source
 */
class Dat extends AbstractSource
{
    const DEFAULT_POSTCODE_LENGTH = 4;
    const XXX_COUNT = 3;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @var array
     */
    protected $lengthByCountry = [];

    /**
     * Dat constructor.
     *
     * @param \Magento\Framework\App\Filesystem\DirectoryList $directoryList
     * @param \Magento\Framework\Filesystem\Driver\File $fileSystemDriver
     * @param ScopeConfigInterface $scopeConfig
     * @param SerializerInterface $serializer
     */
    public function __construct(
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\Filesystem\Driver\File $fileSystemDriver,
        ScopeConfigInterface $scopeConfig,
        SerializerInterface $serializer
    ) {
        $this->serializer = $serializer;
        $this->scopeConfig = $scopeConfig;
        parent::__construct($directoryList, $fileSystemDriver);
    }

    /**
     * @return array
     */
    public function parse()
    {
        $content = $this->fileGetContents();
        $rows = explode("\n", $content);
        $result = [];
        foreach ($rows as $data) {
            if (!empty($data)) {
                $rowData = explode(' ', $data);
                $contentData = '';
                $countRows = count($rowData);
                for ($i = 0; $i <= $countRows; $i++) {
                    if (!empty($rowData[$i])) {
                        $contentData .= ' ' . $rowData[$i];
                    }
                    if (empty($rowData[$i + 1])) {
                        break;
                    }
                }
                $result[] = [
                    'postcode' => $this->extractPostCode($contentData),
                    'suburb' => $this->extractSuburb($contentData),
                    'store_id' => $this->storeId,
                    'website_id' => $this->websiteId,
                    'country_code' => $this->countryCode,
                ];
            }
        }
        return $result;
    }

    /**
     * @param string $contentData
     * @return string
     */
    protected function extractPostCode($contentData)
    {
        if(empty($contentData))
        {
            return $contentData;
        }
        return substr(trim($contentData), 0, $this->getCountryPostcodeLength());
    }

    /**
     * @param string $contentData
     * @return string
     */
    protected function extractSuburb($contentData)
    {
        if(empty($contentData))
        {
            return $contentData;
        }
        return substr(trim($contentData), $this->getSuburbStartLine());
    }

    /**
     * @return int|mixed
     */
    protected function getSuburbStartLine()
    {
        return $this->getCountryPostcodeLength() + static::XXX_COUNT;
    }

    /**
     * @return int|mixed
     */
    protected function getCountryPostcodeLength()
    {
        if (empty($this->lengthByCountry)) {
            try {
                $value = $this->scopeConfig->getValue('digidirect_address_suggestion/general/postcode_length');
                $value = $this->serializer->unserialize($value);
                if (is_array($value) && !empty($value)) {
                    $this->prepareLocationsConfiguration($value);
                }
            } catch (\Throwable $throwable) {
                return static::DEFAULT_POSTCODE_LENGTH;
            }
        }

        return $this->lengthByCountry[$this->countryCode] ?? static::DEFAULT_POSTCODE_LENGTH;
    }

    /**
     * @param array $value
     * @return void
     */
    protected function prepareLocationsConfiguration(array $value = [])
    {
        foreach ($value as $key => $configuration) {
            $country = $configuration['country'] ?? null;
            $postcodeLength = $configuration['postcode_length'] ?? static::DEFAULT_POSTCODE_LENGTH;
            $this->lengthByCountry[$country] = $postcodeLength;
        }
    }
}
