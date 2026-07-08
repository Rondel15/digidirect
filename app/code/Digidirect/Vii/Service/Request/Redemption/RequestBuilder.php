<?php

namespace Digidirect\Vii\Service\Request\Redemption;

use Digidirect\Vii\Service\Request\AbstractBuilder;
use Digidirect\AbstractGiftCard\Service\Helper\SubjectReader;
use Digidirect\AbstractGiftCard\Service\Request\BuilderInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Psr\Log\LoggerInterface;

/**
 * Class RequestBuilder
 * @package Digidirect\Vii\Service\Request\Redemption
 */
class RequestBuilder extends AbstractBuilder implements BuilderInterface
{
    const REQUEST_TYPE = 'Redemption';
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
        $this->logger->info('Starting redemption request build process', [
            'build_subject_keys' => array_keys($buildSubject)
        ]);

        try {
            /** @var \Digidirect\AbstractGiftCard\Service\Data\ServiceDataObject $serviceDO */
            $serviceDO = SubjectReader::readService($buildSubject);
            $store = $serviceDO->getService()->getStore();
            $this->initRequestParams($store);
            $data = ['amount' => SubjectReader::readAmount($buildSubject)];

            $this->logger->debug('Initial request data prepared');

            try {
                $token = SubjectReader::readToken($buildSubject);
                $data['token'] = $token;
                $this->logger->debug('Token added to request data');
                //@codingStandardsIgnoreStart
            } catch (\InvalidArgumentException $e) {
                $this->logger->info('No token provided in build subject', [
                    'exception_message' => $e->getMessage()
                ]);
            }
            //@codingStandardsIgnoreEnd

            $requestDataObject = $this->createRequestDataObject($data);
            $result = ['xml' => $this->getRequestBody($serviceDO, $requestDataObject)];

            $this->logger->info('Redemption request build completed successfully');

            return $result;

        } catch (\Exception $e) {
            $this->logger->error('Error building redemption request', [
                'exception_message' => $e->getMessage(),
                'exception_trace' => $e->getTraceAsString()
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
        try {
            $service = $serviceDO->getService();
            $entity = $service->getAbstractGiftCardEntity();
            $order = $service->getOrder();
            $requestParams = parent::getRequestParams();

            $transId = $requestParams[self::REQUEST_TRAN_ID_FIELD];
            $service->setLastTransId($transId);

            $requestParams[self::REQUEST_CARD_NUMBER_FIELD] = $entity->getData('code');
            $requestParams[self::REQUEST_PIN_FIELD] = $entity->getData('pin');
            $requestParams[self::REQUEST_AMOUNT_FIELD] = number_format($requestDataObject->getData('amount'), 2, '.', '');
            $requestParams[self::REQUEST_EXTERNAL_REFERENCE] = $order->getQuoteId();

            if ($requestDataObject->getData('token')) {
                $requestParams[self::REQUEST_AUTH_CODE] = $requestDataObject->getData('token');
                $this->logger->debug('Auth code added to request parameters');
            }

            $this->logger->info('Request body parameters prepared', [
                'transaction_id' => $transId,
                'quote_id' => $order->getQuoteId(),
                'amount' => $requestParams[self::REQUEST_AMOUNT_FIELD],
                'has_auth_code' => !empty($requestParams[self::REQUEST_AUTH_CODE])
            ]);

            $xmlBody = $this->assocToXml($requestParams, self::SECTION_ROOT);

            $this->logger->debug('XML request body generated', [
                'xml_length' => strlen($xmlBody)
            ]);

            return $xmlBody;

        } catch (\Exception $e) {
            $this->logger->error('Error generating request body', [
                'exception_message' => $e->getMessage(),
                'exception_trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
}