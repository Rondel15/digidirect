<?php
namespace Digidirect\AI\Model\Lib\Entity\Import\Customer\Preparer;

use Digidirect\AI\Model\Lib\Entity\Import\Service\PreparerInterface;

/**
 * Class ArrayToString
 *
 * Converts array values to comma-separated string value for specified columns
 */
class ArrayToString implements PreparerInterface
{
    /**
     * @var array
     */
    protected $columnsToConvert;

    /**
     * ArrayToString constructor.
     *
     * @param array $columnsToConvert
     */
    public function __construct(array $columnsToConvert = [])
    {
        $this->columnsToConvert = $columnsToConvert;
    }

    /**
     * @param array $entity
     * @return void
     */
    public function prepareEntity(array &$entity)
    {
        foreach ($this->columnsToConvert as $columnKey) {
            if (isset($entity[$columnKey]) && is_array($entity[$columnKey])) {
                $entity[$columnKey] = implode(',', $entity[$columnKey]);
            }
        }
    }
}
