<?php
namespace Digidirect\ExtendedShippingRates\Model;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\AbstractModel;

abstract class AbstractRepository
{
    /**
     * @var AbstractDb
     */
    protected $resource;

    /**
     * @var object
     */
    protected $factory;

    /**
     * AbstractRepository constructor.
     * @param AbstractDb $resource
     * @param mixed $factory
     */
    public function __construct(
        AbstractDb $resource,
        $factory
    ) {
        $this->resource = $resource;
        $this->factory = $factory;
    }

    /**
     * Save data
     *
     * @param AbstractModel $model
     * @return AbstractModel
     * @throws CouldNotSaveException
     */
    public function save(AbstractModel $model)
    {
        try {
            $this->resource->save($model);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the model: %1',
                $exception->getMessage()
            ));
        }
        return $model;
    }

    /**
     * Load data by given identity
     *
     * @param int $id
     * @return AbstractModel
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($id)
    {
        /**
         * @var AbstractModel $model
         */
        $model = $this->factory->create();
        $this->resource->load($model, $id);
        if (!$model->getId()) {
            throw new NoSuchEntityException(__('Entity with id "%1" does not exist.', $id));
        }
        return $model;
    }

    /**
     * Delete model by given identity
     *
     * @param int $id
     * @return AbstractDb
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($id)
    {
        return $this->resource->delete($this->getById($id));
    }
}
