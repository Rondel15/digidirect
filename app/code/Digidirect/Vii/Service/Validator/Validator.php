<?php

namespace Digidirect\Vii\Service\Validator;

use Digidirect\AbstractGiftCard\Service\Command\CommandException;
use Digidirect\AbstractGiftCard\Service\Validator\AbstractValidator;
use Digidirect\AbstractGiftCard\Service\Validator\ValidatorInterface;
use Digidirect\AbstractGiftCard\Service\Helper\SubjectReader;
use Psr\Log\LoggerInterface;
use Digidirect\AbstractGiftCard\Service\Validator\ResultInterfaceFactory;
use Digidirect\Vii\Service\Exeption\ServiceExeption;

/**
 * Class Validator
 * @package Digidirect\Vii\Service\Validator
 */
class Validator extends AbstractValidator implements ValidatorInterface
{
    const SECTION_RESPONSE_CODE = 'ResponseCode';
    const SECTION_RESPONSE_MESSAGE = 'ResponseMessage';
    const SUCCESS_RESPONSE_CODE = '0';

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Validator constructor.
     * @param ResultInterfaceFactory $resultFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        ResultInterfaceFactory $resultFactory,
        LoggerInterface $logger
    ) {
        parent::__construct($resultFactory);
        $this->logger = $logger;
    }

    /**
     * @param array $validationSubject
     * @return \Digidirect\AbstractGiftCard\Service\Validator\ResultInterface
     */
    public function validate(array $validationSubject)
    {
        $response = SubjectReader::readResponse($validationSubject);
        try {
            if (!isset($response[self::SECTION_RESPONSE_CODE])) {
                throw new ServiceExeption(__('Response structure is not valid.'));
            }

            if ($response[self::SECTION_RESPONSE_CODE] !== self::SUCCESS_RESPONSE_CODE) {
                throw new CommandException(__("%1", $response[self::SECTION_RESPONSE_MESSAGE]));
            }
        } catch (CommandException $e) {
            return $this->_createResult(false, [$e->getMessage()]);
        } catch (ServiceExeption $exception) {
            return $this->_createResult(false, [$exception->getMessage()]);
        }
        $service = SubjectReader::readService($validationSubject);
        $service->setCheckResponse($response);
        return $this->_createResult(true);
    }
}
