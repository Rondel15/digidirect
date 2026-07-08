<?php

namespace Digidirect\AbstractGiftCard\Service\Helper;

use Digidirect\AbstractGiftCard\Service\Data\ServiceDataObjectInterface;
use Magento\Framework\DataObject;

class SubjectReader
{
    /**
     * Reads service from subject
     *
     * @param array $subject
     * @return ServiceDataObjectInterface
     */
    public static function readService(array $subject)
    {
        if (!isset($subject['service'])
            || !$subject['service'] instanceof ServiceDataObjectInterface
        ) {
            throw new \InvalidArgumentException('Service data object should be provided');
        }

        return $subject['service'];
    }

    /**
     * Reads field from subject
     *
     * @param array $subject
     * @return string
     */
    public static function readField(array $subject)
    {
        if (!isset($subject['field']) || !is_string($subject['field'])) {
            throw new \InvalidArgumentException('Field does not exist');
        }

        return $subject['field'];
    }

    /**
     * Reads response NVP from subject
     *
     * @param array $subject
     * @return array
     */
    public static function readResponse(array $subject)
    {
        if (!isset($subject['response']) || !is_array($subject['response'])) {
            throw new \InvalidArgumentException('Response does not exist');
        }

        return $subject['response'];
    }

    /**
     * Read state object from subject
     *
     * @param array $subject
     * @return DataObject
     */
    public static function readStateObject(array $subject)
    {
        if (!isset($subject['stateObject']) || !$subject['stateObject'] instanceof DataObject) {
            throw new \InvalidArgumentException('State object does not exist');
        }

        return $subject['stateObject'];
    }

    /**
     * Reads amount from subject
     *
     * @param array $subject
     * @return mixed
     */
    public static function readAmount(array $subject)
    {
        if (!isset($subject['amount']) || !is_numeric($subject['amount'])) {
            throw new \InvalidArgumentException('Amount should be provided');
        }

        return $subject['amount'];
    }

    /**
     * Reads token from subject
     *
     * @param array $subject
     * @return mixed
     */
    public static function readToken(array $subject)
    {
        if (!isset($subject['token']) && empty($subject['token'])) {
            throw new \InvalidArgumentException('Invalid Token');
        }

        return $subject['token'];
    }

    /**
     * Reads cancellation reason from subject
     *
     * @param array $subject
     * @return string
     */
    public static function readCancellationReason(array $subject)
    {
        if (!isset($subject['reason'])) {
            throw new \InvalidArgumentException('Invalid cancellation reason');
        }

        return $subject['reason'];
    }
}
