<?php
namespace Digidirect\AbstractAttributes\Ui\DataProvider;

use Digidirect\AbstractAttributes\Model\ResourceModel\AbstractAttribute\CollectionFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Store\Model\Store;

/**
 * Class ProductAdvancedOptionDataProvider
 * @package Digidirect\AbstractAttributes\Ui\DataProvider
 */
class ProductAdvancedOptionDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var \Digidirect\AbstractAttributes\Model\ResourceModel\AbstractAttribute\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var array
     */
    protected $loadedData;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * ProductAdvancedOptionDataProvider constructor.
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param RequestInterface $request
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        RequestInterface $request,
        array $meta = [],
        array $data = []
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->request = $request;

        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->meta = $this->prepareMeta($this->meta);
    }

    /**
     * @return int
     */
    public function getStoreId()
    {
        return $this->request->getParam('store', Store::DEFAULT_STORE_ID);
    }

    /**
     * Get collection
     * @return \Digidirect\AbstractAttributes\Model\ResourceModel\AbstractAttribute\Collection
     */
    public function getCollection()
    {
        if (null === $this->collection) {
            $this->collection = $this->collectionFactory->create();
            $this->collection->addStoreFilter($this->getStoreId(), true);
        }

        return $this->collection;
    }

    /**
     * Prepare meta data
     * @param array $meta
     * @return array
     */
    public function prepareMeta($meta)
    {
        $meta['eaa_properties_fieldset']['children']['aa.listing_enabled']
        ['arguments']['data']['config']['value'] = 0;

        return $meta;
    }

    /**
     * Get data
     * @return array
     */
    public function getData()
    {
        if (!$attributeId = $this->request->getParam('attribute_id')) {
            return parent::getData();
        }

        if (isset($this->loadedData)) {
            return $this->loadedData;
        }

        $items = $this->getCollection()
            ->getItems();

        /** @var \Digidirect\AbstractAttributes\Model\AbstractAttribute $aa */
        foreach ($items as $aa) {
            $aa->setStoreId($this->getStoreId());
            $this->loadedData[$aa->getAttributeId()]['aa'] = $aa->getData();
        }

        return $this->loadedData;
    }
}
