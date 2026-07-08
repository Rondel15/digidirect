<?php

namespace Digidirect\Vii\Service\Helper;

use Digidirect\AbstractGiftCard\Service\Data\ServiceDataObjectInterface;

class SubjectReader extends \Digidirect\AbstractGiftCard\Service\Helper\SubjectReader
{
    /**
     * Reads transaction id from subject
     *
     * @param array $subject
     * @return ServiceDataObjectInterface
     */
    public static function readTransId(array $subject)
    {
        if (!isset($subject['trans_id']) && empty($subject['trans_id'])) {
            throw new \InvalidArgumentException('Invalid Transaction Id');
        }

        return $subject['trans_id'];
    }
}
