<?php
namespace Digidirect\MyStoreWidget\Model\Config\Source;

use Digidirect\MyStoreWidget\Api\MyStoreRepositoryInterface;
use Magento\Framework\Option\ArrayInterface;

/**
 * Class Stores
 * @package Digidirect\MyStoreWidget\Model\Config\Source
 */
class Stores implements ArrayInterface
{
    /**
     * @var MyStoreRepositoryInterface
     */
    protected $myStoreRepository;

    /**
     * @var array
     */
    protected $_options;

    /**
     * Stores constructor.
     * @param MyStoreRepositoryInterface $myStoreRepository
     */
    public function __construct(
        MyStoreRepositoryInterface $myStoreRepository
    ) {
        $this->myStoreRepository = $myStoreRepository;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        if ($this->_options === null) {
            $this->_options = [];
            $this->_options[] = [
                'value' => '',
                'label' => '',
            ];
            $collection = $this->myStoreRepository->getStoresCollection();
            foreach ($collection as $abstractEntity) {
                $this->_options[] = [
                    'value' => $abstractEntity->getEntityId(),
                    'label' => $abstractEntity->getName(),
                ];
            }
        }
        return $this->_options;
    }
}
