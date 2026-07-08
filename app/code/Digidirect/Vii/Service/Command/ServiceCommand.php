<?php

namespace Digidirect\Vii\Service\Command;

use Digidirect\AbstractGiftCard\Api\AbstractGiftCardLoggerInterface as LoggerInterface;
use Digidirect\AbstractGiftCard\Service\Http\ClientInterface;
use Digidirect\AbstractGiftCard\Service\Http\TransferFactoryInterface;
use Digidirect\AbstractGiftCard\Service\Request\BuilderInterface;
use Digidirect\AbstractGiftCard\Service\Response\HandlerInterface;
use Digidirect\AbstractGiftCard\Service\Validator\ValidatorInterface;
use Digidirect\Vii\Service\Config\Config;
use Digidirect\AbstractGiftCard\Service\Http\ConverterException;
use Digidirect\AbstractGiftCard\Service\Helper\SubjectReader;
use Digidirect\Vii\Service\Exeption\ServiceExeption;
use Magento\Framework\Session\SessionManager;
use Monolog\Logger as MonologLogger;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\State;
use Magento\Framework\App\Area;

/**
 * Class ServiceCommand
 * @package Digidirect\Vii\Service\Command
 */
class ServiceCommand extends \Digidirect\AbstractGiftCard\Service\Command\ServiceCommand
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var string
     */
    protected $commandCode;

    /**
     * @var null
     */
    protected $canBeReverted;

    /**
     * @var null
     */
    protected $placeAgainOnError;

    /**
     * @var SessionManager
     */
    protected $session;

    /**
     * @var State
     */
    protected $stateArea;

    /**
     * ServiceCommand constructor.
     * @param BuilderInterface $requestBuilder
     * @param TransferFactoryInterface $transferFactory
     * @param ClientInterface $client
     * @param LoggerInterface $logger
     * @param Config $config
     * @param SessionManager $session
     * @param State $stateArea
     * @param HandlerInterface|null $handler
     * @param ValidatorInterface|null $validator
     * @param null $commandCode
     * @param null $canBeReverted
     * @param null $placeAgainOnError
     */
    public function __construct(
        BuilderInterface $requestBuilder,
        TransferFactoryInterface $transferFactory,
        ClientInterface $client,
        LoggerInterface $logger,
        Config $config,
        SessionManager $session,
        State $stateArea,
        HandlerInterface $handler = null,
        ValidatorInterface $validator = null,
        $commandCode = null,
        $canBeReverted = null,
        $placeAgainOnError = null
    ) {
        parent::__construct(
            $requestBuilder,
            $transferFactory,
            $client,
            $logger,
            $handler,
            $validator,
            $commandCode
        );
        $this->config = $config;
        $this->commandCode = $commandCode;
        $this->canBeReverted = $canBeReverted;
        $this->placeAgainOnError = $placeAgainOnError;
        $this->session = $session;
        $this->stateArea = $stateArea;
    }

    /**
     * @param \Digidirect\AbstractGiftCard\Service\Http\TransferInterface $transferO
     * @param array $commandSubject
     * @param \Exception $e
     * @return array|\Digidirect\AbstractGiftCard\Service\Command\ServiceCommand|null
     * @throws LocalizedException
     */
    protected function doOnCatch($transferO, $commandSubject, \Exception $e)
    {
        $this->_logExceptions([$e->getMessage()]);
        if ($e instanceof ConverterException) {
            return null;
        }
        $serviceDO = SubjectReader::readService($commandSubject);
        /** @var \Digidirect\AbstractGiftCard\Model\Service\Adapter $service */
        $service = $serviceDO->getService();

        if ($this->canBeReverted) {
            $this->setDataToRevertTransaction($service);
            $this->throwNotRespondingMessage($service->getStore());
        }

        if ($this->placeAgainOnError) {
            try {
                $response = $this->_client->placeRequest($transferO);
            } catch (ConverterException $exception) {
                $this->_logExceptions([$exception->getMessage()]);
                $response = null;
            } catch (\Exception $exception) {
                $this->_logExceptions([$exception->getMessage()]);
                $response = null;
                $this->throwNotRespondingMessage($service->getStore());
            }
            return $response;
        }

        throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()));
    }

    /**
     * @param null|int $storeId
     * @return void
     * @throws LocalizedException
     */
    public function throwNotRespondingMessage($storeId = null)
    {
        $mapping = $this->config->getNotRespondingMessageMapping($storeId);
        $message = isset($mapping[$this->commandCode])
            ? $mapping[$this->commandCode]
            : Config::NOT_RESPONDING_DEFAULT_MESSAGE;
        throw new ServiceExeption(__($message));
    }

    /**
     * Set previous transaction requested data to session
     * @param \Digidirect\AbstractGiftCard\Model\Service\Adapter $service
     * @return $this
     * @throws LocalizedException
     */
    protected function setDataToRevertTransaction($service)
    {
        $area = $this->stateArea->getAreaCode();
        if ($area == Area::AREA_CRONTAB) {
            return $this;
        }
        $transId = $service->getLastTransId();
        $this->session->setData('last_failed_transaction_id', $transId);
        $entity = $service->getAbstractGiftCardEntity();
        $this->session->setData('last_failed_abstract_gift_card_entity', $entity->getId());
        return $this;
    }

    /**
     * @param \Digidirect\AbstractGiftCard\Service\Validator\ResultInterface $result
     * @param \Digidirect\AbstractGiftCard\Service\Http\TransferInterface $transferO
     * @param array $commandSubject
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function onValidationFailure($result, $transferO, $commandSubject)
    {
        $this->_logExceptions($result->getFailsDescription(), MonologLogger::INFO);
        $response = SubjectReader::readResponse($commandSubject);
        if (!isset($response['ResponseCode'])) {
            $serviceDO = SubjectReader::readService($commandSubject);
            /** @var \Digidirect\AbstractGiftCard\Model\Service\Adapter $service */
            $service = $serviceDO->getService();
            if ($this->canBeReverted) {
                $this->setDataToRevertTransaction($service);
                $this->throwNotRespondingMessage($service->getStore());
            }
        }

        throw new \Magento\Framework\Exception\LocalizedException(
            __(current($result->getFailsDescription()))
        );
    }
}
