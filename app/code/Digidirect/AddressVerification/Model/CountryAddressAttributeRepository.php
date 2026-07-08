<?php
namespace Digidirect\AddressVerification\Model;

use Digidirect\AddressVerification\Api\CountryAddressAttributeRepositoryInterface;
use Digidirect\AddressVerification\Api\Data\CountryAddressAttributeInterface;
use Digidirect\AddressVerification\Model\CountryAddress\Source\Provider\AttributeProvider;
use Digidirect\AddressVerification\Model\ResourceModel\CountryAddressAttribute;
use Digidirect\AddressVerification\Model\ResourceModel\CountryAddressAttribute\CollectionFactory;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class CountryAddressAttributeRepository
 * @package Digidirect\AddressVerification\Model
 */
class CountryAddressAttributeRepository implements CountryAddressAttributeRepositoryInterface
{
    /**
     * @var CountryAddressAttribute
     */
    protected $resourceModel;

    /**
     * @var CountryAddressAttribute
     */
    protected $modelFactory;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Directory\Model\CountryFactory
     */
    protected $countryFactory;

    /**
     * CountryAddressAttributeRepository constructor.
     * @param CountryAddressAttribute $resourceModel
     * @param CountryAddressAttributeFactory $modelFactory
     * @param CollectionFactory $collectionFactory
     * @param \Magento\Directory\Model\CountryFactory $countryFactory
     */
    public function __construct(
        CountryAddressAttribute $resourceModel,
        CountryAddressAttributeFactory $modelFactory,
        CollectionFactory $collectionFactory,
        \Magento\Directory\Model\CountryFactory $countryFactory
    ) {
        $this->resourceModel = $resourceModel;
        $this->modelFactory = $modelFactory;
        $this->collectionFactory = $collectionFactory;
        $this->countryFactory = $countryFactory;
    }

    /**
     * @param int $id
     * @return \Digidirect\AddressVerification\Model\CountryAddressAttribute
     */
    public function getById($id)
    {
        $item = $this->modelFactory->create();
        $this->resourceModel->load($item, $id);
        return $item;
    }
    
    /**
     * @param string $code
     * @return \Digidirect\AddressVerification\Model\CountryAddressAttribute
     */
    public function getByCountryCode($code)
    {
        $item = $this->modelFactory->create();
        $this->resourceModel->load($item, $code, CountryAddressAttributeInterface::COUNTRY_CODE_FIELD);
        return $item;
    }

    /**
     * @param int $id
     * @return $this
     */
    public function deleteById($id)
    {
        return $this->resourceModel->delete($this->getById($id));
    }

    /**
     * @param \Digidirect\AddressVerification\Model\CountryAddressAttribute $item
     * @return \Digidirect\AddressVerification\Model\CountryAddressAttribute
     * @throws LocalizedException
     * @throws \Exception
     */
    public function save(\Digidirect\AddressVerification\Model\CountryAddressAttribute $item)
    {
        $code = $item->getCountryCode();
        $attribute = $this->getByCountryCode($code);
        if ($attribute->getId() && $attribute->getId() != $item->getId()) {
            throw new LocalizedException(
                __('Item with Country "%1" already exist', $this->getCountryNameByCode($code))
            );
        }
        
        $requiredAttributes = [AttributeProvider::ATTRIBUTE_POSTCODE, AttributeProvider::ATTRIBUTE_SUBURB];
        if (!empty(array_diff($requiredAttributes, $item->getAttributes()))) {
            throw new LocalizedException(
                __(
                    'Postcode and City attributes are not allowed for 
                            unselecting in the current revision of extension'
                )
            );
        }
        $item->encodeAttributes();
        $this->resourceModel->save($item);
        return $item;
    }

    /**
     * @param string $countryCode
     * @return string
     */
    public function getCountryNameByCode($countryCode)
    {
        return $this->countryFactory->create()->loadByCode($countryCode)->getName();
    }
    
    /**
     * @param string $countryCode
     * @param bool $decode
     * @return array
     */
    public function getAddressAttributesByCountryCode($countryCode, $decode = true)
    {

        $item = $this->getByCountryCode($countryCode);
        $attributes = '{}';
        if ($item->getId()) {
            $attributes = $item->getAttributes();
        }
        if ($decode === true) {
            $attributes = $item->getDecodedAttributes();
        }
        return $attributes;
    }

    /**
     * @return CountryAddressAttribute\Collection
     */
    public function getAll()
    {
        return $this->collectionFactory->create();
    }
}
