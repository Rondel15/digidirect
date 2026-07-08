<?php

namespace Digidirect\AbstractGiftCard\Service\Command;

use Magento\Framework\Phrase;
use Digidirect\AbstractGiftCard\Service\CommandInterface;
use Digidirect\AbstractGiftCard\Service\Http\ClientInterface;
use Digidirect\AbstractGiftCard\Service\Http\TransferFactoryInterface;
use Digidirect\AbstractGiftCard\Service\Request\BuilderInterface;
use Digidirect\AbstractGiftCard\Service\Response\HandlerInterface;
use Digidirect\AbstractGiftCard\Service\Validator\ValidatorInterface;
use Digidirect\AbstractGiftCard\Api\AbstractGiftCardLoggerInterface as LoggerInterface;
use Monolog\Logger as MonologLogger;

/**
 * Class GatewayCommand
 * @api
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ServiceCommand implements CommandInterface
{
    /**
     * @var BuilderInterface
     */
    protected $_requestBuilder;

    /**
     * @var TransferFactoryInterface
     */
    protected $_transferFactory;

    /**
     * @var ClientInterface
     */
    protected $_client;

    /**
     * @var HandlerInterface
     */
    protected $_handler;

    /**
     * @var ValidatorInterface
     */
    protected $_validator;

    /**
     * @var LoggerInterface
     */
    protected $_logger;

    /**
     * @param BuilderInterface $requestBuilder
     * @param TransferFactoryInterface $transferFactory
     * @param ClientInterface $client
     * @param LoggerInterface $logger
     * @param HandlerInterface $handler
     * @param ValidatorInterface $validator
     * @param string $commandCode
     */
    public function __construct(
        BuilderInterface $requestBuilder,
        TransferFactoryInterface $transferFactory,
        ClientInterface $client,
        LoggerInterface $logger,
        HandlerInterface $handler = null,
        ValidatorInterface $validator = null,
        $commandCode = null
    ) {
        $this->_requestBuilder = $requestBuilder;
        $this->_transferFactory = $transferFactory;
        $this->_client = $client;
        $this->_handler = $handler;
        $this->_validator = $validator;
        $this->_logger = $logger;
        $this->_logger->setCommandCode($commandCode);
    }

    /**
     * Executes command basing on business object
     *
     * @param array $commandSubject
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(array $commandSubject)
    {
        // @TODO implement exceptions catching
        $store = null;
        if (isset($commandSubject['service'])) {
            $service = $commandSubject['service'];
            $store = $service->getService()->getStore();
        }
        $transferO = $this->_transferFactory->create(
            $this->_requestBuilder->build($commandSubject),
            $store
        );

        try {
            $response = $this->_client->placeRequest($transferO);
        } catch (\Exception $e) {
            if (!$this->doOnCatch($transferO, $commandSubject, $e)) {
                return;
            }
        }

        if ($this->_validator !== null) {
            $result = $this->_validator->validate(
                array_merge($commandSubject, ['response' => $response])
            );
            if (!$result->isValid()) {
                $this->onValidationFailure(
                    $result,
                    $transferO,
                    array_merge($commandSubject, ['response' => $response])
                );
            }
        }

        if ($this->_handler) {
            $this->_handler->handle(
                $commandSubject,
                $response
            );
        }
    }

    /**
     * @param Phrase[] $fails
     * @param int $level
     * @return void
     */
    protected function _logExceptions(array $fails, $level = MonologLogger::ERROR)
    {
        foreach ($fails as $failPhrase) {
            $this->_logger->debug((string)$failPhrase, $level);
        }
    }

    /**
     * @param \Digidirect\AbstractGiftCard\Service\Http\TransferInterface $transferO
     * @param array $commandSubject
     * @param \Exception $e
     * @return |null
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function doOnCatch($transferO, $commandSubject, \Exception $e)
    {
        $this->_logExceptions([$e->getMessage()]);
        return null;
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
        throw new \Magento\Framework\Exception\LocalizedException(
            __(current($result->getFailsDescription()))
        );
    }
}
