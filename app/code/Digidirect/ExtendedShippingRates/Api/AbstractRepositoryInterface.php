<?php
namespace Digidirect\ExtendedShippingRates\Api;

interface AbstractRepositoryInterface
{
    /**
     * Save model.
     *
     * @param \Magento\Framework\Model\AbstractModel $model
     * @return \Magento\Framework\Model\AbstractModel
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(\Magento\Framework\Model\AbstractModel $model);

    /**
     * Retrieve model.
     *
     * @param int $id
     * @return \Magento\Framework\Model\AbstractModel
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($id);

    /**
     * Delete model.
     *
     * @param int $id
     * @return \Magento\Framework\Model\ResourceModel\Db\AbstractDb
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($id);
}
