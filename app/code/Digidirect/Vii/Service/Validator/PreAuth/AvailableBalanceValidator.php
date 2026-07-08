<?php

namespace Digidirect\Vii\Service\Validator\PreAuth;

use Digidirect\AbstractGiftCard\Service\Command\CommandException;
use Digidirect\AbstractGiftCard\Service\Helper\SubjectReader;
use Digidirect\AbstractGiftCard\Service\Validator\ResultInterfaceFactory;
use Digidirect\Vii\Service\Response\CheckStatus\Handler as CheckStatusHandle;

/**
 * Class AvailableBalanceValidator
 * @package Digidirect\Vii\Service\Validator\PreAuth
 */
class AvailableBalanceValidator extends \Digidirect\Vii\Service\Validator\Validator
{
    const PREAUTH_CODE = 'PreAuthCode';
    const AMOUNT = 'Amount';

    /**
     * @param array $validationSubject
     * @return \Digidirect\AbstractGiftCard\Service\Validator\ResultInterface
     */
    public function validate(array $validationSubject)
    {
        $response = SubjectReader::readResponse($validationSubject);
        try {
            if ($response[self::SECTION_RESPONSE_CODE] !== self::SUCCESS_RESPONSE_CODE) {
                if (!isset($response[self::PREAUTH_CODE])
                    && isset($response[self::AMOUNT])
                    && isset($response[CheckStatusHandle::BALANCE])
                    && (float)$response[self::AMOUNT] > (float)$response[CheckStatusHandle::BALANCE]
                    && (float)$response[CheckStatusHandle::BALANCE] > 0) {
                    $serviceDO = SubjectReader::readService($validationSubject);
                    $serviceDO->getService()->setAvailableBalance($response[CheckStatusHandle::BALANCE]);
                }
                throw new CommandException(__("%1", $response[self::SECTION_RESPONSE_MESSAGE]));
            }
        } catch (CommandException $e) {
            return $this->_createResult(false, [$e->getMessage()]);
        }
        $service = SubjectReader::readService($validationSubject);
        $service->setCheckResponse($response);
        return $this->_createResult(true);
    }
}
