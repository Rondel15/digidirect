<?php

namespace Digidirect\Vii\Service\Request\PreAuth;

use Digidirect\Vii\Service\Request\AbstractBuilder;
use Digidirect\AbstractGiftCard\Service\Helper\SubjectReader;
use Digidirect\AbstractGiftCard\Service\Request\BuilderInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Psr\Log\LoggerInterface;

/**
 * Class RequestBuilder
 * @package Digidirect\Vii\Service\Request\PreAuth
 */
class RequestBuilder extends AbstractBuilder implements BuilderInterface
{
    const REQUEST_TYPE = 'PreAuthRequest';

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * RequestBuilder constructor.
     * @param LoggerInterface $logger
     * @param \Digidirect\Vii\Service\Config\Config $config
     * @param \Magento\Framework\Math\Random $math
     * @param \Magento\Config\Model\Config\Backend\Encrypted $encrypted
     * @param array $layout
     */
    public function __construct(
        \Digidirect\Vii\Service\Config\Config $config,
        \Magento\Framework\Math\Random $math,
        \Magento\Config\Model\Config\Backend\Encrypted $encrypted,
        LoggerInterface $logger,
        array $layout = []

    ) {
        parent::__construct($config, $math, $encrypted, $layout);
        $this->logger = $logger;
    }

    /**
     * @param array $buildSubject
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function build(array $buildSubject)
    {
        $this->logger->info('Starting PreAuth request build process', [
            'request_type' => self::REQUEST_TYPE,
            'build_subject_keys' => array_keys($buildSubject)
        ]);

        try {
            /** @var \Digidirect\AbstractGiftCard\Service\Data\ServiceDataObject $serviceDO */
            $serviceDO = SubjectReader::readService($buildSubject);
            $store = $serviceDO->getService()->getStore();
            $amount = SubjectReader::readAmount($buildSubject);

            $this->logger->debug('PreAuth request data extracted');

            $this->initRequestParams($store);

            $requestDataObject = $this->createRequestDataObject(['amount' => $amount]);
            $xmlBody = $this->getRequestBody($serviceDO, $requestDataObject);

            $result = ['xml' => $xmlBody];

            $this->logger->info('PreAuth request build completed successfully', [
                'xml_length' => strlen($xmlBody)
            ]);

            return $result;

        } catch (\Exception $e) {
            $this->logger->error('Error building PreAuth request', [
                'exception_message' => $e->getMessage(),
                'exception_trace' => $e->getTraceAsString(),
                'request_type' => self::REQUEST_TYPE
            ]);
            throw $e;
        }
    }

    /**
     * @param \Digidirect\AbstractGiftCard\Service\Data\ServiceDataObject $serviceDO
     * @param \Magento\Framework\DataObject $requestDataObject = null
     * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
     * @return string
     */
    public function getRequestBody(
        \Digidirect\AbstractGiftCard\Service\Data\ServiceDataObject $serviceDO,
        \Magento\Framework\DataObject $requestDataObject = null
    ) {
        $this->logger->debug('Generating PreAuth request body');

        try {
            $entity = $serviceDO->getService()->getAbstractGiftCardEntity();
            $quote = $serviceDO->getQuote();
            $requestParams = parent::getRequestParams();

            // Log business context without sensitive data
            $this->logger->debug('PreAuth request context prepared', [
                'quote_id' => $quote->getId(),
                'quote_currency' => $quote->getQuoteCurrencyCode(),
                'entity_id' => $entity->getId(),
                'amount' => $requestDataObject ? $requestDataObject->getData('amount') : null
            ]);

            $requestParams[self::REQUEST_CARD_NUMBER_FIELD] = $entity->getData('code');
            $requestParams[self::REQUEST_PIN_FIELD] = $entity->getData('pin');
            $requestParams[self::REQUEST_AMOUNT_FIELD] = number_format($requestDataObject->getData('amount'), 2, '.', '');
            $requestParams[self::REQUEST_EXTERNAL_REFERENCE] = $quote->getId();

            $this->logger->info('PreAuth request parameters prepared', [
                'amount_formatted' => $requestParams[self::REQUEST_AMOUNT_FIELD],
                'external_reference' => $requestParams[self::REQUEST_EXTERNAL_REFERENCE],
                'transaction_id' => $requestParams[self::REQUEST_TRAN_ID_FIELD] ?? 'not_set'
            ]);

            $xmlBody = $this->assocToXml($requestParams, self::SECTION_ROOT);

            $this->logger->debug('PreAuth XML request body generated', [
                'xml_length' => strlen($xmlBody),
                'section_root' => self::SECTION_ROOT
            ]);

            return $xmlBody;

        } catch (\Exception $e) {
            $this->logger->error('Error generating PreAuth request body', [
                'exception_message' => $e->getMessage(),
                'exception_trace' => $e->getTraceAsString(),
                'request_type' => self::REQUEST_TYPE
            ]);
            throw $e;
        }
    }
}