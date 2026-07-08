<?php
namespace Digidirect\AI\Model\Lib\Entity\Export\Product;

use Digidirect\AI\Model\Lib\Entity\Export\ExportAbstract;

class Product extends ExportAbstract implements ProductInterface
{
    const ENTITY_NAME = 'product';

    /**
     * @return string
     */
    public function getName()
    {
        return self::ENTITY_NAME;
    }
}
