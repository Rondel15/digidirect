<?php

namespace Digidirect\Collect\Helper\Storage;

use Digidirect\Collect\Helper\Data as CollectHelperData;
use Digidirect\Collect\Model\PostCode as PostCodeModel;
use Digidirect\Collect\Model\ResourceModel\PostCode as PostCodeResource;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Data
 *
 * @package Digidirect\Collect\Helper\Storage
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const GOOGLE_GEOCODE_API_URL = 'https://maps.googleapis.com/maps/api/geocode/json';
    const XML_GOOGLE_API_KEY = 'carriers/collect/google_api_key';

    const DEFAULT_COUNTRY_ID = 'AU';

    /**
     * Curl
     *
     * @var \Magento\Framework\HTTP\Client\Curl
     */
    protected $_curl;

    /**
     * Decoder
     *
     * @var \Magento\Framework\Json\DecoderInterface
     */
    protected $_decoder;

    /**
     * CountryInformation
     *
     * @var \Magento\Directory\Api\CountryInformationAcquirerInterface
     */
    protected $_countryInformation;

    /**
     * CheckoutSession
     *
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var PostCodeResource
     */
    protected $postCodeResource;

    /**
     * Data constructor.
     *
     * @param Context $context
     * @param \Magento\Directory\Api\CountryInformationAcquirerInterface $countryInformation
     * @param \Magento\Framework\HTTP\Client\Curl $curl
     * @param \Magento\Framework\Json\DecoderInterface $decoder
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param PostCodeResource $postCodeResource
     */
    public function __construct(
        Context $context,
        \Magento\Directory\Api\CountryInformationAcquirerInterface $countryInformation,
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Magento\Framework\Json\DecoderInterface $decoder,
        \Magento\Checkout\Model\Session $checkoutSession,
        PostCodeResource $postCodeResource
    ) {
        parent::__construct($context);
        $this->_countryInformation = $countryInformation;
        $this->_curl = $curl;
        $this->_decoder = $decoder;
        $this->_checkoutSession = $checkoutSession;
        $this->postCodeResource = $postCodeResource;
    }

    /**
     * Get coordinates long and lat by postcode
     *
     * @param int $postcode
     * @return []
     */
    public function getCoordinatesByPostcode($postcode)
    {
        if ($this->getGeoLocationMethod() == CollectHelperData::GEO_LOCATION_METHOD_POST_CODE) {
            $coordinatesData = $this->postCodeResource->getCoordinatesByPostCodeFromDB($postcode);
            $coordinates = [
                'lat' => $coordinatesData[PostCodeModel::TABLE_COLUMN_LATITUDE],
                'long' => $coordinatesData[PostCodeModel::TABLE_COLUMN_LONGITUDE]
            ];

            return $coordinates;
        }
        //generate url for request to google api to get coordinates by postcode
        $url = $this->generateGoogleApiUrl($postcode);
        //curl request
        $this->_curl->setHeaders(['Accept: application/json']);
        $this->_curl->setOption(CURLOPT_RETURNTRANSFER, true);
        $this->_curl->setOption(CURLOPT_SSL_VERIFYPEER, false);
        $this->_curl->setOption(CURLOPT_TIMEOUT, 90);
        try {
            $this->_curl->get($url);
            $jsonData = $this->_curl->getBody();
            $data = $this->_decoder->decode($jsonData);
            if (!$data || empty($data['results'])) {
                return false;
            }
        } catch (\Exception $e) {
            $this->_logger->error('COLLECT ERROR ==>> ' . $e->getMessage());

            return false;
        }

        //calculate average coordinates
        $latCount = 0;
        $longCount = 0;
        $latSum = 0;
        $longSum = 0;
        foreach ($data['results'] as $item) {
            if (isset($item['geometry']) &&
                isset($item['geometry']['location']) &&
                isset($item['geometry']['location']['lat']) &&
                isset($item['geometry']['location']['lng'])
            ) {
                $latCount++;
                $longCount++;
                $latSum += $item['geometry']['location']['lat'];
                $longSum += $item['geometry']['location']['lng'];
            }
        }

        $long = 0;
        $lat = 0;
        if ($latCount && $longCount) {
            $long = $longSum / $longCount;
            $lat = $latSum / $latCount;
        }

        $coordinates = ['lat' => $lat, 'long' => $long];

        return $coordinates;
    }

    /**
     * GetDistanceBetweenCoordinates
     *
     * @param float $lat1
     * @param float $lon1
     * @param float $lat2
     * @param float $lon2
     * @param string $measure = 'K' - is kilometers, else = miles
     * @return float|null
     */
    public function getDistanceBetweenCoordinates($lat1, $lon1, $lat2, $lon2, $measure = 'K')
    {
        $theta = $lon1 - $lon2;
        $dist = (sin(deg2rad($lat1)) * sin(deg2rad($lat2))) +
            (cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta)));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $miles = $dist * 60 * 1.1515;
        $measure = strtoupper($measure);

        $result = null;
        if ($measure == "K") {
            $result = ($miles * 1.609344);
        } else {
            $result = $miles;
        }

        return round($result, 2);
    }

    /**
     * Get google api key
     *
     * @return mixed|string
     */
    protected function getGoogleApiKey()
    {
        return $this->scopeConfig->getValue(
            self::XML_GOOGLE_API_KEY,
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * Generate google api url for request
     *
     * @param string $postcode
     * @return string
     */
    protected function generateGoogleApiUrl($postcode)
    {
        if (is_numeric($postcode)) {
            $queryType = 'postal_code';
        } else {
            $queryType = 'locality';
        }

        return self::GOOGLE_GEOCODE_API_URL .
            '?loading=async&components=country:' . $this->getCountryId() . '|' . $queryType . ':' .
            (string)$postcode . '&key=' . $this->getGoogleApiKey();
    }

    /**
     * GetCountryId
     *
     * @return mixed
     */
    public function getCountryId()
    {
        return $this->scopeConfig->getValue(
            'general/store_information/country_id',
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * Get stock update interface
     *
     * @return mixed|string
     */
    public function getDefaultDistanceRange()
    {
        return $this->scopeConfig->getValue(
            CollectHelperData::XML_DEFAULT_DISTANCE_RANGE,
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * GetCheckoutSession
     *
     * @return \Magento\Checkout\Model\Session
     */
    public function getCheckoutSession()
    {
        return $this->_checkoutSession;
    }

    /**
     * Whether collect is enable
     *
     * @return bool
     */
    public function getGeoLocationMethod()
    {
        return $this->scopeConfig->getValue(
            CollectHelperData::XML_GEO_LOCATION_METHOD,
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * @return bool
     */
    public function showUnavailablePlaces()
    {
        return $this->scopeConfig->isSetFlag(
            CollectHelperData::CONFIG_SHOW_UNAVAILABLE_PLACES,
            ScopeInterface::SCOPE_STORE
        );
    }
}
