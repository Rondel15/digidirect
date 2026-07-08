<?php
namespace Digidirect\Blog\Api;

/**
 * Interface AbstractRepositoryInterface
 */
interface AbstractRepositoryInterface
{
    /**
     * Get object by ID
     *
     * @param int $id
     * @return \Magento\Framework\Model\AbstractModel
     */
    public function getById($id);

    /**
     * Delete record by ID
     *
     * @param int $id
     * @return bool
     */
    public function deleteById($id);
}
