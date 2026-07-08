<?php
namespace Digidirect\AI\Model\Lib\Entity\Export;

use Digidirect\AI\Model\Lib\Entity\EntityInterface;
use Magento\Framework\DB\Select;

interface ExportInterface extends EntityInterface
{
    /**
     * @return Select
     */
    public function getSelect();

    /**
     * @return string[]
     */
    public function fetchAll();

    /**
     * @return $this
     */
    public function distinct();
}
