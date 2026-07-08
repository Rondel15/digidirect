<?php
namespace Digidirect\AbstractAttributes\Ui\Component\Form;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Ui\Component\Form\FieldFactory;
use Magento\Ui\Component\Form\Fieldset as BaseFieldset;

/**
 * Class LabelFieldset
 * @package Digidirect\AbstractAttributes\Ui\Component\Form
 */
class LabelFieldset extends BaseFieldset
{
    /**
     * @var StoreRepositoryInterface
     */
    protected $_store;

    /**
     * @var FieldFactory
     */
    private $fieldFactory;

    /**
     * LabelFieldset constructor.
     * @param ContextInterface $context
     * @param FieldFactory $fieldFactory
     * @param StoreRepositoryInterface $store
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        FieldFactory $fieldFactory,
        StoreRepositoryInterface $store,
        array $components = [],
        array $data = []
    ) {
        $this->_store = $store;

        parent::__construct($context, $components, $data);

        $this->fieldFactory = $fieldFactory;
    }

    /**
     * Get components
     * @return UiComponentInterface[]
     */
    public function getChildComponents()
    {
        $components = $this->components;

        $stores = $this->_store->getList();
        $sortOrder = 0;
        foreach ($stores as $store) {
            if ($storeId = $store->getId()) {
                $sortOrder += 10;
                $name = 'label_' . $storeId;
                $field = $components['label_0'];
                $config = $field->getConfiguration();
                $config['dataScope'] = $name;
                $config['sortOrder'] = $sortOrder;
                $config['label'] = $store->getName() . ' Label';
                $config['validation']['required-entry'] = false;
                $newField = $this->fieldFactory->create();
                $newField->setData([
                    'config' => $config,
                    'name'   => $name
                ]);
                $newField->prepare();
                $this->addComponent($name, $newField);
            }
        }

        return parent::getChildComponents();
    }
}
