<?php
namespace Digidirect\AbstractEntity\Ui\DataProvider\Form;

use Magento\Ui\DataProvider\AbstractDataProvider;
use Digidirect\AbstractEntity\Model\ResourceModel\AbstractEntity\CollectionFactory as AbstractEntityCollectionFactory;
use Magento\Framework\App\RequestInterface;
use Digidirect\AbstractEntity\Api\Data\AbstractEntityInterface;

/**
 * Class RelationDataProvider
 * @package Digidirect\AbstractEntity\Ui\DataProvider\Form
 */
class RelationDataProvider extends AbstractDataProvider
{
    /**
     * @var AbstractEntityCollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * RelationDataProvider constructor.
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param AbstractEntityCollectionFactory $collectionFactory
     * @param RequestInterface $request
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        AbstractEntityCollectionFactory $collectionFactory,
        RequestInterface $request,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->_collectionFactory = $collectionFactory;
        $this->request = $request;
        $this->collection = $this->_collectionFactory->create();
    }

    /**
     * @return array
     */
    public function getData()
    {
        $this->getCollection()->addFieldToFilter(
            AbstractEntityInterface::PARENT_ID,
            $this->request->getParam('current_entity_id', 0)
        )
            ->addFieldToSelect(AbstractEntityInterface::NAME)
            ->addFieldToSelect(AbstractEntityInterface::STATUS);

        $arrItems = [
            'totalRecords' => $this->getCollection()->getSize(),
            'items' => [],
        ];

        foreach ($this->getCollection() as $item) {
            $arrItems['items'][] = $item->toArray([]);
        }

        return $arrItems;
    }
}
