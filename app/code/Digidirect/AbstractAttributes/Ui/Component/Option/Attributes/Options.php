<?php
namespace Digidirect\AbstractAttributes\Ui\Component\Option\Attributes;

use Digidirect\AbstractAttributes\Api\AbstractAttributeRepositoryInterface;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Options
 * @package Digidirect\AbstractAttributes\Ui\Component\Option\Attributes
 */
class Options implements OptionSourceInterface
{
    /**
     * @var AbstractAttributeRepositoryInterface
     */
    protected $_abstractAttributeRepository;

    /**
     * Options constructor.
     * @param AbstractAttributeRepositoryInterface $abstractAttributeRepository
     */
    public function __construct(
        AbstractAttributeRepositoryInterface $abstractAttributeRepository
    ) {
        $this->_abstractAttributeRepository = $abstractAttributeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        $abstractAttributes = $this->_abstractAttributeRepository->getAbstractAttributes(true);
        $response = [['label' => '', 'value' => '']];
        foreach ($abstractAttributes as $abstractAttribute) {
            $response[] = [
                'label' => $abstractAttribute->getAttributeLabel(),
                'value' => $abstractAttribute->getAttributeId()
            ];
        }
        return $response;
    }
}
