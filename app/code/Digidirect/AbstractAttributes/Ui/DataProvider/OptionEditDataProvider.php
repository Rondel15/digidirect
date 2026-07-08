<?php
namespace Digidirect\AbstractAttributes\Ui\DataProvider;

use Digidirect\AbstractAttributes\Helper\Image as ImageHelper;
use Digidirect\AbstractAttributes\Model\ResourceModel\Option\Grid\CollectionFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Store;
use Magento\Backend\Model\Session;

/**
 * Class OptionEditDataProvider
 * @package Digidirect\AbstractAttributes\Ui\DataProvider
 */
class OptionEditDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var \Digidirect\AbstractAttributes\Model\ResourceModel\Option\Grid\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var array
     */
    protected $loadedData;

    /**
     * @var ImageHelper
     */
    protected $imageHelper;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var Session
     */
    protected $session;

    /**
     * DataProvider constructor.
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param ImageHelper $imageHelper
     * @param RequestInterface $request
     * @param Session $session
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        ImageHelper $imageHelper,
        RequestInterface $request,
        Session $session,
        array $meta = [],
        array $data = []
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->imageHelper = $imageHelper;
        $this->request = $request;
        $this->session = $session;

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
     * @return \Digidirect\AbstractAttributes\Model\ResourceModel\Option\Grid\Collection
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
        $attrId = $this->request->getParam('attribute_id');
        if ($attrId) {
            $meta['general']['children']['back_to_edit_attribute_id']['arguments']['data']['config']['value'] = $attrId;
            $meta['general']['children']['attribute_id']['arguments']['data']['config']['value'] = $attrId;
        }

        $optId = $this->request->getParam('option_id');
        if ($attrId || $optId) {
            $meta['general']['children']['attribute_id']['arguments']['data']['config']['disabled'] = true;
        }

        if (!$optId) {
            $meta['general']['children']['status']['arguments']['data']['config']['value'] = true;
            $meta['general']['children']['include_in_widget']['arguments']['data']['config']['value'] = true;
        }

        return $meta;
    }

    /**
     * Get data
     * @return array
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }

        $this->loadedData = [];
        $items = $this->getCollection()->getItems();
        /** @var \Digidirect\AbstractAttributes\Model\Option $option */
        foreach ($items as $option) {
            $option->setStoreId($this->getStoreId());
            $this->imageHelper->addOptionImagesInfo($option);
            $this->loadedData[$option->getOptionId()] = $option->getData();
        }

        $formData = $this->session->getEaaOptionData();
        if (!empty($formData)) {
            $newOption = $this->collection->getNewEmptyItem();
            $newOption->addData($formData);
            $this->loadedData[$newOption->getId()] = $newOption->getData();
            $this->session->unsEaaOptionData();
        }

        return $this->loadedData;
    }
}
