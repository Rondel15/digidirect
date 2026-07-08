<?php

namespace Digidirect\MyStoreWidget\Model\Indexer\Store;

use Digidirect\AbstractEntity\Api\Data\AbstractEntityInterface;
use Digidirect\AbstractEntity\Model\ResourceModel\AbstractEntity\CollectionFactory;
use Digidirect\MyStoreWidget\Model\ResourceModel\MyStoreIndex;
use Digidirect\MyStoreWidget\Helper\Config as Helper;
use Digidirect\MyStoreWidget\Spi\AdditionalAttributeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\ObjectManager;

class Action
{
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var MyStoreIndex
     */
    protected $myStoreIndex;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var array
     */
    protected $additionalAttributes;

    /**
     * Action constructor.
     * @param CollectionFactory $collectionFactory
     * @param MyStoreIndex $myStoreIndex
     * @param Helper $helper
     * @param StoreManagerInterface|null $storeManager
     * @param array $additionalAttributes
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        MyStoreIndex $myStoreIndex,
        Helper $helper,
        StoreManagerInterface $storeManager = null,
        $additionalAttributes = []
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->myStoreIndex = $myStoreIndex;
        $this->helper = $helper;
        $this->storeManager = $storeManager ?: ObjectManager::getInstance()->get(StoreManagerInterface::class);
        $this->additionalAttributes = $additionalAttributes;
    }

    /**
     * Get additional attributes
     * @return array
     */
    protected function getAdditionalAttributes()
    {
        $attributes = [];

        if (count($this->additionalAttributes)) {
            foreach ($this->additionalAttributes as $attribute) {
                if ($attribute instanceof AdditionalAttributeInterface) {
                    $code = $attribute->getCode();

                    if (!empty($code)) {
                        $attributes[] = $code;
                    }
                }
            }
        }

        return $attributes;
    }

    /**
     * @param array $ids
     * @return void
     */
    public function reindex(array $ids = [])
    {
        $attributes = $this->helper->getAttributes();
        $responseAttributes = $this->helper->getResponseAttributes();
        $additionalAttributes = $this->getAdditionalAttributes();

        if ($additionalAttributes) {
            $responseAttributes = array_unique(array_merge($responseAttributes, $additionalAttributes));
        }

        foreach ($this->storeManager->getStores(true) as $store) {
            $storeCollection = $this->collectionFactory->create();
            $storeCollection->setFlag('reindex', true);

            $storeCollection
                ->setStoreId($store->getId())
                ->addAttributeToSelect(AbstractEntityInterface::NAME, true)
                ->addAttributeToSelect($attributes, true)
                ->addAttributeToSelect($responseAttributes, true)
                ->addFieldToFilter(AbstractEntityInterface::STATUS, 1)
                ->addFieldToFilter(AbstractEntityInterface::ATTRIBUTE_SET_ID, ['in' => $this->helper->getEntities()]);

            $this->myStoreIndex->createTableFromSelect($storeCollection->getSelect(), $attributes, $store->getId());
        }
    }
}
