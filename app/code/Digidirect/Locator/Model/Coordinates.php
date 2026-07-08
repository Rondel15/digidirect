<?php

namespace Digidirect\Locator\Model;

use \Magento\Framework\HTTP\Adapter\CurlFactory;
use \Magento\Directory\Model\CountryFactory;
use Digidirect\GoogleApi\Helper\Config as Helper;

/**
 * Since 1.2.0 it implements interface
 */
class Coordinates implements CoordinatesGetterInterface
{
    /**
     *  Google Map Api Url
     */
    const GOOGLE_MAP_URL = 'https://maps.googleapis.com/maps/api/geocode/';

    /**
     * @var CurlFactory
     */
    protected $curlFactory;

    /**
     * @var CountryFactory
     */
    protected $countryFactory;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var array
     */
    protected $headers;

    /**
     * Coordinates constructor.
     * @param CurlFactory $curlFactory
     * @param CountryFactory $countryFactory
     * @param Helper $helper
     */
    public function __construct(
        CurlFactory $curlFactory,
        CountryFactory $countryFactory,
        Helper $helper
    ) {
        $this->curlFactory = $curlFactory;
        $this->countryFactory = $countryFactory;
        $this->helper = $helper;
    }

    /**
     * @param string|array $address
     * @param string $serverApiKey
     * @return array|bool
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getCoordinatesByAddress($address, $serverApiKey = null)
    {
        $street = isset($address['street']) ? $address['street'] : false;
        $city = isset($address['city']) ? $address['city'] : false;
        $country = isset($address['country']) ? $address['country'] : false;
        $region = isset($address['state']) ? $address['state'] : false;
        $postcode = isset($address['postcode']) ? $address['postcode'] : false;
        $countryName = $this->_getCountryName($country);

        $prepareAddress = '';

        if ($street) {
            $prepareAddress .= $street;
        }

        if ($city) {
            $prepareAddress .= ',' . $city;
        }

        if ($region) {
            $prepareAddress .= ',' . $region;
        }

        if ($countryName) {
            $prepareAddress .= ',' . $countryName;
        }

        if ($postcode) {
            $prepareAddress .= ',' . $postcode;
        }

        $this->headers = [];
        $prepareAddress = urlencode($prepareAddress);
        if ($serverApiKey === null) {
            $serverApiKey = $this->helper->getGoogleApiKey();
        }
        $url = self::GOOGLE_MAP_URL . 'json?loading=async&key=' . $serverApiKey . '&address=' . $prepareAddress;
        try {
            $http = $this->curlFactory->create();
            $config = ['timeout' => 5, 'header' => false, 'verifypeer' => false];
            $http->setConfig($config);
            $http->write(
                \Zend_Http_Client::POST,
                $url
            );

            $response = $http->read();

            if ($response === false) {
                return false;
            }

            $json = $this->_parseResponseText($response);
            $step1 = $json['results'][0];
            $step2 = $step1['geometry'];
            $coords = $step2['location'];

            return $coords;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @param string $country
     * @return bool|string
     */
    private function _getCountryName($country)
    {
        if ($country) {
            $country = $this->countryFactory->create()->loadByCode($country);
            return $country->getName();
        }

        return false;
    }

    /**
     * @param string $response
     * @return mixed
     * @throws \Exception
     */
    private function _parseResponseText($response)
    {
        $p = strpos($response, "\r\n\r\n");
        if ($p !== false) {
            $response = substr($response, $p + 4);
        }

        $json = json_decode($response, true);
        if (isset ($json->error)) {
            throw new \Exception($json->message);
        }

        return $json;
    }
}
