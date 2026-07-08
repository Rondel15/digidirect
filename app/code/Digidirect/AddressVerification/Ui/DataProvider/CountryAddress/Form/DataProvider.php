<?php
namespace Digidirect\AddressVerification\Ui\DataProvider\CountryAddress\Form;

use Digidirect\AddressVerification\Model\CountryAddress\Source\Provider\AttributeProvider;
use Digidirect\AddressVerification\Model\CountryAddressAttribute;
use Digidirect\AddressVerification\Model\ResourceModel\CountryAddressAttribute\CollectionFactory as CollectionFactory;
use Magento\Ui\DataProvider\AbstractDataProvider;

/**
 * Class DataProvider
 * @package Digidirect\AddressVerification\Ui\DataProvider\CountryAddress\Form
 */
class DataProvider extends AbstractDataProvider
{

    /**
     * DataProvider constructor.
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
    }

    /**
     * Used to be able modify data
     *
     * @return array
     */
    public function getData()
    {
        $items = $this->collection->getItems();
        if (empty($items)) {
            $this->data[0] = [
                'attributes' => [AttributeProvider::ATTRIBUTE_POSTCODE, AttributeProvider::ATTRIBUTE_SUBURB]
            ];
        }
        /**
         * @var CountryAddressAttribute $item
         */
        foreach ($items as $item) {
            $this->data[$item->getId()] = $item->getData();
        }
        return $this->data;
    }
}
