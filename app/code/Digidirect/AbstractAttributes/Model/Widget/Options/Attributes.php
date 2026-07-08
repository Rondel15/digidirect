<?php
namespace Digidirect\AbstractAttributes\Model\Widget\Options;

use Digidirect\AbstractAttributes\Api\AbstractAttributeRepositoryInterface;
use Magento\Framework\Option\ArrayInterface;

/**
 * Class Attributes
 * @package Digidirect\AbstractAttributes\Model\Widget\Options
 */
class Attributes implements ArrayInterface
{
    /**
     * @var AbstractAttributeRepositoryInterface
     */
    protected $repository;

    /**
     * Attributes constructor.
     * @param AbstractAttributeRepositoryInterface $attributeRepository
     */
    public function __construct(
        AbstractAttributeRepositoryInterface $attributeRepository
    ) {
        $this->repository = $attributeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        $aAttributes = $this->repository->getAbstractAttributes(true);

        $result = [];
        foreach ($aAttributes as $aa) {
            $result[] = ['label' => $aa->getAttributeLabel(), 'value' => $aa->getAttributeId()];
        }

        return $result;
    }
}
