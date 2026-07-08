<?php
namespace Digidirect\AI\Model\Lib\Entity\Import;

use Digidirect\AI\Model\Lib\Entity\EntityInterface;

interface ImportInterface extends EntityInterface
{
    /**
     * @param array $data
     * @param bool $updateOnDuplicate
     * @return bool
     */
    public function save(array $data, $updateOnDuplicate = true);

    /**
     * @param array $data
     * @param bool $updateOnDuplicate
     * @return bool
     */
    public function saveBunch(array $data, $updateOnDuplicate = true);

    /**
     * @param array $data
     * @return bool
     */
    public function update(array $data);

    /**
     * @param array $data
     * @return bool
     */
    public function updateBunch(array $data);
}
