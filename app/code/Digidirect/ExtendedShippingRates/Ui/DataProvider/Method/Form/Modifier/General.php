<?php
namespace Digidirect\ExtendedShippingRates\Ui\DataProvider\Method\Form\Modifier;

use Digidirect\ExtendedShippingRates\Api\Data\MethodInterface;
use Digidirect\ExtendedShippingRates\Controller\Adminhtml\ExtendedShippingRates\Method as MethodController;
use Digidirect\ExtendedShippingRates\Model\Carrier\MethodFactory;
use Digidirect\ExtendedShippingRates\Model\Config\Source\WeightType;
use Digidirect\ExtendedShippingRates\Model\ResourceModel\Carrier\CollectionFactory as CarrierCollectionFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\Component\Form\Element\DataType\Number;
use Magento\Ui\Component\Form\Element\DataType\Text;
use Magento\Ui\Component\Form\Element\Hidden;
use Magento\Ui\Component\Form\Element\Select;
use Magento\Ui\Component\Form\Field;

/**
 * Data provider for main panel
 */
class General extends AbstractModifier
{
    const KEY_SUBMIT_URL = 'submit_url';

    const GENERAL_FIELDSET_NAME = 'general';
    const FIELD_CARRIER_ID_NAME = 'carrier_id';

    /**
     * @var array
     */
    protected $meta = [];

    /**
     * @var CarrierCollectionFactory
     */
    protected $carrierCollectionFactory;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @param ArrayManager $arrayManager
     * @param UrlInterface $urlBuilder
     * @param MethodFactory $methodFactory
     * @param Registry $coreRegistry
     * @param StoreManagerInterface $storeManager
     * @param CarrierCollectionFactory $carrierCollectionFactory
     * @param RequestInterface $request
     */
    public function __construct(
        ArrayManager $arrayManager,
        UrlInterface $urlBuilder,
        MethodFactory $methodFactory,
        Registry $coreRegistry,
        StoreManagerInterface $storeManager,
        CarrierCollectionFactory $carrierCollectionFactory,
        RequestInterface $request
    ) {
        parent::__construct($arrayManager, $urlBuilder, $methodFactory, $coreRegistry, $storeManager);
        $this->carrierCollectionFactory = $carrierCollectionFactory;
        $this->request = $request;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        // Add submit (save) url to the config
        $actionParameters = [];
        $submitUrl = $this->urlBuilder->getUrl(
            'digidirect_extendedshippingrates/extendedshippingrates_method/save',
            $actionParameters
        );
        $data = array_replace_recursive(
            $data,
            [
                'config' => [
                    self::KEY_SUBMIT_URL => $submitUrl,
                ]
            ]
        );

        foreach ($data as &$entity) {
            if (array_key_exists(MethodInterface::PACKAGING_WEIGHT_TYPE, $entity)
                && empty($entity[MethodInterface::PACKAGING_WEIGHT_TYPE])
            ) {
                $entity[MethodInterface::PACKAGING_WEIGHT_TYPE] = WeightType::EMPTY_CODE;
            }
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {
        $this->meta = $meta;
        $this->addCarrierFieldConfig();

        return $this->meta;
    }

    /**
     * Add carriers input:
     * hidden for existing method and select with all available carriers for the new method
     * hidden input for the 'back_to' param (if need to redirect admin back to the carrier edit form page)
     *
     * @return void
     */
    protected function addCarrierFieldConfig()
    {
        // Carrier id form field (hidden for existing method or select for the new method)
        $method = $this->getMethod();
        if ($method->getData('entity_id')) {
            $carrierFieldConfig = [
                'label' => __('Carrier'),
                'componentType' => Field::NAME,
                'formElement' => Select::NAME,
                'disabled' => true,
                'dataScope' => static::FIELD_CARRIER_ID_NAME,
                'dataType' => Number::NAME,
                'sortOrder' => 0,
                'options' => $this->getCarriers()
            ];
        } elseif ($this->request->getParam('carrier_id')) {
            $carrierFieldConfig = [
                'label' => __('Carrier'),
                'componentType' => Field::NAME,
                'formElement' => Select::NAME,
                'disabled' => true,
                'dataScope' => static::FIELD_CARRIER_ID_NAME,
                'dataType' => Number::NAME,
                'sortOrder' => 0,
                'options' => $this->getCarriers(),
                'value' => $this->request->getParam('carrier_id')
            ];
        } else {
            $carrierFieldConfig = [
                'label' => __('Carrier'),
                'componentType' => Field::NAME,
                'formElement' => Select::NAME,
                'dataScope' => static::FIELD_CARRIER_ID_NAME,
                'dataType' => Text::NAME,
                'sortOrder' => 0,
                'options' => $this->getCarriers(),
                'disableLabel' => true,
                'multiple' => false,
            ];
        }

        $result[static::GENERAL_FIELDSET_NAME]['children'][static::FIELD_CARRIER_ID_NAME] = [
            'arguments' => [
                'data' => [
                    'config' => $carrierFieldConfig
                ],
            ],
        ];

        // The "back_to" hidden input, if need to redirect admin back to the carrier edit form
        if ($this->request->getParam(MethodController::BACK_TO_PARAM)) {
            $result[static::GENERAL_FIELDSET_NAME]['children'][MethodController::BACK_TO_PARAM] = [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'label' => '',
                            'componentType' => Field::NAME,
                            'formElement' => Hidden::NAME,
                            'dataScope' => MethodController::BACK_TO_PARAM,
                            'dataType' => Number::NAME,
                            'sortOrder' => 0,
                            'value' => $this->request->getParam(MethodController::BACK_TO_PARAM)
                        ]
                    ],
                ],
            ];
        }

        $this->meta = array_replace_recursive(
            $this->meta,
            $result
        );
    }

    /**
     * Get the carriers collection as an option array
     *
     * @return array
     */
    protected function getCarriers()
    {
        /** @var \Digidirect\ExtendedShippingRates\Model\ResourceModel\Carrier\Collection $carrierCollection */
        $carrierCollection = $this->carrierCollectionFactory->create();
        $result = $carrierCollection->toOptionArray();

        return $result;
    }
}
