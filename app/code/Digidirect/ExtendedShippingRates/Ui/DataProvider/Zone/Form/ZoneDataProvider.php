<?php
namespace Digidirect\ExtendedShippingRates\Ui\DataProvider\Zone\Form;

use Digidirect\ExtendedShippingRates\Model\ResourceModel\Zone\CollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Magento\Ui\DataProvider\Modifier\PoolInterface;

class ZoneDataProvider extends AbstractDataProvider
{
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var PoolInterface
     */
    protected $pool;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param PoolInterface $pool
     * @param RequestInterface $request
     * @param DataPersistorInterface $dataPersistor
     * @param StoreManagerInterface $storeManager
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        PoolInterface $pool,
        RequestInterface $request,
        DataPersistorInterface $dataPersistor,
        StoreManagerInterface $storeManager,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->pool = $pool;
        $this->request = $request;
        $this->storeManager = $storeManager;
        $this->collection = $collectionFactory->create();
    }

    /**
     * Get meta from all modifiers
     *
     * @return array
     */
    public function getMeta()
    {
        $meta = parent::getMeta();
        $modifiers = $this->pool->getModifiersInstances();
        /** @var ModifierInterface $modifier */
        foreach ($modifiers as $modifier) {
            $meta = $modifier->modifyMeta($meta);
        }

        return $meta;
    }

    /**
     * Get data from all modifiers and items from the collection
     *
     * @return array
     */
    public function getData()
    {
        if (!empty($this->data)) {
            return $this->data;
        }
        $items = $this->collection->getItems();
        $isSingleStore = $this->storeManager->isSingleStoreMode();
        /** @var \Digidirect\ExtendedShippingRates\Model\Carrier\Method $method */
        foreach ($items as $method) {
            $this->data[$method->getId()] = $method->getData();
            $this->data[$method->getId()]['single_store'] = $isSingleStore;
        }

        $modifiers = $this->pool->getModifiersInstances();
        /** @var ModifierInterface $modifier */
        foreach ($modifiers as $modifier) {
            $this->data = $modifier->modifyData($this->data);
        }

        return $this->data;
    }
}
