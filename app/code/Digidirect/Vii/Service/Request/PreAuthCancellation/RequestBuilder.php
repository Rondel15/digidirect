<?php

namespace Digidirect\Vii\Service\Request\PreAuthCancellation;

use Digidirect\Vii\Service\Request\AbstractBuilder;
use Digidirect\AbstractGiftCard\Service\Helper\SubjectReader;
use Digidirect\AbstractGiftCard\Service\Data\ServiceDataObjectInterface;
use Digidirect\AbstractGiftCard\Service\Request\BuilderInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Quote\Api\Data\CartInterface;
use Psr\Log\LoggerInterface;

/**
 * Class RequestBuilder
 * @package Digidirect\Vii\Service\Request\PreAuthCancellation
 */
class RequestBuilder extends AbstractBuilder implements BuilderInterface
{
    const REQUEST_TYPE = 'PreAuthCancellation';
    const REQUEST_AUTH_CODE = 'PreAuthCode';

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
        $this->logger->info('Starting PreAuth cancellation request build process', [
            'request_type' => self::REQUEST_TYPE,
            'build_subject_keys' => array_keys($buildSubject)
        ]);

        try {
            /** @var \Digidirect\AbstractGiftCard\Service\Data\ServiceDataObject $serviceDO */
            $serviceDO = SubjectReader::readService($buildSubject);
            $store = $serviceDO->getService()->getStore();
            $token = SubjectReader::readToken($buildSubject);

            $this->logger->debug('PreAuth cancellation data extracted');

            $this->initRequestParams($store);

            $requestDataObject = $this->createRequestDataObject(['token' => $token]);
            $xmlBody = $this->getRequestBody($serviceDO, $requestDataObject);

            $result = ['xml' => $xmlBody];

            $this->logger->info('PreAuth cancellation request build completed successfully', [
                'xml_length' => strlen($xmlBody)
            ]);

            return $result;

        } catch (\InvalidArgumentException $e) {
            $this->logger->error('Invalid token provided for PreAuth cancellation', [
                'exception_message' => $e->getMessage(),
                'request_type' => self::REQUEST_TYPE
            ]);
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error('Error building PreAuth cancellation request', [
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
        $this->logger->debug('Generating PreAuth cancellation request body');

        try {
            $entity = $serviceDO->getService()->getAbstractGiftCardEntity();
            $service = $serviceDO->getService();
            $object = $service->getOrder() ?? ($service->getQuote() ?? $serviceDO->getQuote());

            // Determine object type and get quote ID
            $isOrder = $object instanceof OrderInterface;
            $quoteId = $isOrder ? $object->getQuoteId() : $object->getId();
            $objectType = $isOrder ? 'order' : 'quote';

            $this->logger->debug('PreAuth cancellation context prepared', [
                'entity_id' => $entity->getId(),
                'object_type' => $objectType,
                'object_id' => $object->getId(),
                'quote_id' => $quoteId,
                'has_auth_token' => $requestDataObject && !empty($requestDataObject->getData('token'))
            ]);

            $requestParams = parent::getRequestParams();
            $requestParams[self::REQUEST_CARD_NUMBER_FIELD] = $entity->getData('code');
            $requestParams[self::REQUEST_EXTERNAL_REFERENCE] = $quoteId;
            $requestParams[self::REQUEST_AUTH_CODE] = $requestDataObject->getData('token');

            $this->logger->info('PreAuth cancellation request parameters prepared', [
                'external_reference' => $quoteId,
                'object_type' => $objectType,
                'transaction_id' => $requestParams[self::REQUEST_TRAN_ID_FIELD] ?? 'not_set',
                'has_auth_code' => !empty($requestParams[self::REQUEST_AUTH_CODE])
            ]);

            $xmlBody = $this->assocToXml($requestParams, self::SECTION_ROOT);

            $this->logger->debug('PreAuth cancellation XML request body generated', [
                'xml_length' => strlen($xmlBody),
                'section_root' => self::SECTION_ROOT
            ]);

            return $xmlBody;

        } catch (\Exception $e) {
            $this->logger->error('Error generating PreAuth cancellation request body', [
                'exception_message' => $e->getMessage(),
                'exception_trace' => $e->getTraceAsString(),
                'request_type' => self::REQUEST_TYPE
            ]);
            throw $e;
        }
    }
}