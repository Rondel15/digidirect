<?php
namespace Digidirect\AbstractEntity\Ui\DataProvider\Form\Modifier;

use Digidirect\AbstractEntity\Api\Data\AbstractEntityInterface;
use Digidirect\AbstractEntity\Model\ResourceModel\AbstractEntity\CollectionFactory;
use Digidirect\AbstractEntity\Model\Registry\Constants;
use Digidirect\AbstractEntity\Helper\Image as ImageHelper;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Registry;

class Data extends AbstractModifier
{
    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var \Digidirect\AbstractEntity\Model\ResourceModel\AbstractEntity\Collection
     */
    protected $collection;

    /**
     * @var ImageHelper
     */
    protected $imageHelper;

    /**
     * Attributes constructor.
     * @param CollectionFactory $collectionFactory
     * @param DataPersistorInterface $dataPersistor
     * @param Registry $registry
     * @param RequestInterface $request
     * @param ImageHelper $imageHelper
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        DataPersistorInterface $dataPersistor,
        Registry $registry,
        RequestInterface $request,
        ImageHelper $imageHelper
    ) {
        $this->collection = $collectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        $this->imageHelper = $imageHelper;
        parent::__construct(
            $registry,
            $request
        );
    }

    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        $this->collection->addAttributeToSelect('*');
        $this->collection->setStoreId($this->getStoreId());
        $this->collection->addFieldToFilter(
            AbstractEntityInterface::ENTITY_ID,
            $this->getCurrentEntity()->getId()
        );

        $items = $this->collection->getItems();
        foreach ($items as $model) {
            $model->setStoreId($this->getStoreId());
            $this->imageHelper->addImagesInfo($model);
            $data[$model->getId()] = $model->getData();
        }

        $storedData = $this->dataPersistor->get(Constants::CURRENT_ABSTRACT_ENTITY);
        if (!empty($storedData)) {
            $model = $this->collection->getNewEmptyItem();
            $model->setData($storedData);
            $data[$model->getId()] = $model->getData();
            $this->dataPersistor->clear(Constants::CURRENT_ABSTRACT_ENTITY);
        }

        if (!$this->request->getParam('id') && empty($storedData)) {
            $model = $this->collection->getNewEmptyItem();
            $model->setData([AbstractEntityInterface::ATTRIBUTE_SET_ID =>
                $this->request->getParam(AbstractEntityInterface::ATTRIBUTE_SET_ID)]);
            $data[$model->getId()] = $model->getData();
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {
        return $meta;
    }
}
