<?php
namespace Digidirect\AI\Model\Lib\Entity\Export\Customer;

use Digidirect\AI\Model\Lib\Entity\Export\ExportAbstract;

class Customer extends ExportAbstract implements CustomerInterface
{
    const ENTITY_NAME = 'customer';

    /**
     * @return string
     */
    public function getName()
    {
        return self::ENTITY_NAME;
    }
}
