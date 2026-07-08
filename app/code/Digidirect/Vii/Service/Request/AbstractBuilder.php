<?php

namespace Digidirect\Vii\Service\Request;

use Digidirect\AbstractGiftCard\Service\Request\Builder;
use Digidirect\AbstractGiftCard\Service\Helper\ContextHelper;
use Digidirect\AbstractGiftCard\Service\Data\ServiceDataObjectInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Math\Random;
use Magento\Framework\Simplexml\Element as SimplexmlEl;
use Magento\Config\Model\Config\Backend\Encrypted;
use Digidirect\AbstractGiftCard\Service\Helper\SubjectReader;
use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Class RefundDataBuilder
 * @package Digidirect\Vii\Service\Request
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
abstract class AbstractBuilder extends Builder
{
    /**
     * @var string
     */
    const REQUEST_TYPE = 'CheckBalance';
    const REQUEST_TYPE_FIELD = 'RequestType';
    const REQUEST_USERNAME_FIELD = 'viiUserName';
    const REQUEST_PASSWORD_FIELD = 'viiPassword';
    const REQUEST_TRAN_ID_FIELD = 'viiTranId';
    const REQUEST_STORE_ID_FIELD = 'StoreId';
    const REQUEST_CARD_NUMBER_FIELD = 'CardNumber';
    const REQUEST_PIN_FIELD = 'PIN';
    const SECTION_ROOT = 'Request';
    const REQUEST_AMOUNT_FIELD = 'Amount';
    const REQUEST_EXTERNAL_REFERENCE = 'ExternalReference';

    /**
     * @var \Digidirect\Vii\Service\Config\Config
     */
    protected $config;

    /**
     * @var array
     */
    protected $layout = [];

    /**
     * @var array
     */
    protected $requestParams = [];

    /**
     * @var Random
     */
    private $math;

    /**
     * @var Encrypted
     */
    protected $encrypted;

    /**
     * AbstractBuilder constructor.
     * @param \Digidirect\Vii\Service\Config\Config $config
     * @param Random $math
     * @param Encrypted $encrypted
     * @param array $layout
     */
    public function __construct(
        \Digidirect\Vii\Service\Config\Config $config,
        Random $math,
        Encrypted $encrypted,
        array $layout = []
    ) {
        $this->config = $config;
        $this->layout = $layout;
        $this->math = $math;
        $this->encrypted = $encrypted;
    }

    /**
     * @return string
     */
    public function getRequestType()
    {
        return static::REQUEST_TYPE;
    }

    /**
     * @param null|int $storeId
     * @return string
     */
    protected function getUserName($storeId = null)
    {
        return $this->config->getUserName($storeId);
    }

    /**
     * @param null|int $storeId
     * @return string
     */
    protected function getPassword($storeId = null)
    {
        return $this->encrypted->processValue($this->config->getPassword($storeId));
    }

    /**
     * @param null $storeId
     * @return string
     */
    protected function getAccountId($storeId = null)
    {
        return $this->config->getMerchantAccountId($storeId);
    }

    /**
     * @param null $storeId
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function initRequestParams($storeId = null)
    {
        $this->requestParams = [
            self::REQUEST_TYPE_FIELD => $this->getRequestType(),
            self::REQUEST_USERNAME_FIELD => $this->getUserName($storeId),
            self::REQUEST_PASSWORD_FIELD => $this->getPassword($storeId),
            self::REQUEST_TRAN_ID_FIELD => $this->getTransactionId(),
            self::REQUEST_STORE_ID_FIELD => $this->getAccountId($storeId),
        ];
        return $this->requestParams;
    }

    /**
     * @return array
     */
    protected function getRequestParams()
    {
        $variables = $this->requestParams;
        return $variables;
    }

    /**
     * @param \Digidirect\AbstractGiftCard\Service\Data\ServiceDataObject $serviceDO
     * @param \Magento\Framework\DataObject $requestDataObject = null
     * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
     * @return string
     */
    abstract public function getRequestBody(
        \Digidirect\AbstractGiftCard\Service\Data\ServiceDataObject $serviceDO,
        \Magento\Framework\DataObject $requestDataObject = null
    );

    /**
     * @param array $data
     * @return DataObject
     */
    public function createRequestDataObject($data)
    {
        return new DataObject($data);
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getTransactionId()
    {
        return $this->math->getUniqueHash();
    }

    /**
     * @param array $data
     * @param null $root
     * @param array $namespaces
     * @return string
     */
    public static function assocToXml($data, $root = null, $namespaces = [])
    {
        $xml = new SimplexmlEl(!empty($root) ? '<' . $root . '/>' : '<root/>');

        // Register namespace prefixes
        foreach ($namespaces as $prefix => $ns) {
            if ($prefix != '') {
                $xml->registerXPathNamespace($prefix, $ns);
            }
        }

        return self::_assocToXml($data, $xml)->asNiceXml();
    }

    /**
     * Assoc to xml
     *
     * @param [] $data
     * @param SimplexmlEl $xml
     * @return SimplexmlEl
     */
    private static function _assocToXml($data, SimplexmlEl $xml)
    {
        foreach ($data as $key => $value) {
            if ($key == '@' || is_numeric($key)) {
                continue;
            }

            if (!is_array($value)) {
                $xml->addChild($key, $xml->xmlentities($value));
            } elseif (array_unique(array_map("is_int", array_keys($value))) === [true]) {
                //for int array need create array in xml
                $xml->addAttribute('Type', 'Array');
                foreach ($value as $v) {
                    $childXml = self::_assocToXml($v, new SimplexmlEl('<' . $key . '/>'));
                    $xml->appendChild($childXml);
                }
            } else {
                $xml->addChild($key);
                self::_assocToXml($value, $xml->{$key});
            }
        }

        return $xml;
    }
}
