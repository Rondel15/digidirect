<?php

namespace Digidirect\CheckoutFields\Block\Adminhtml\Order\Create\Form;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Sales\Block\Adminhtml\Order\Create\Form\AbstractForm;
use Digidirect\CheckoutFields\Helper\Xml\Fields\Parser;
use Magento\Framework\App\ObjectManager;
use Digidirect\CheckoutFields\Model\OrderFieldValueFactory;

/**
 * Create order additional fields form
 */
class AdditionalFields extends AbstractForm
{
    const DEFAULT_POSITION = 1;

    /**
     * @var Parser|null
     */
    protected $parser = null;

    /**
     * @var OrderFieldValueFactory
     */
    protected $_orderCustomFields;

    /**
     * @var array
     */
    public $checkboxElements = [];

    /**
     * AdditionalFields constructor.
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Model\Session\Quote $sessionQuote
     * @param \Magento\Sales\Model\AdminOrder\Create $orderCreate
     * @param PriceCurrencyInterface $priceCurrency
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Framework\Reflection\DataObjectProcessor $dataObjectProcessor
     * @param Parser $parser
     * @param OrderFieldValueFactory $orderFieldValue
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Model\Session\Quote $sessionQuote,
        \Magento\Sales\Model\AdminOrder\Create $orderCreate,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Reflection\DataObjectProcessor $dataObjectProcessor,
        Parser $parser,
        OrderFieldValueFactory $orderFieldValue,
        array $data = []
    ) {
        $this->parser = $parser;
        $this->_orderCustomFields = $orderFieldValue;

        parent::__construct(
            $context,
            $sessionQuote,
            $orderCreate,
            $priceCurrency,
            $formFactory,
            $dataObjectProcessor,
            $data
        );
    }

    /**
     * Return Header CSS Class
     *
     * @return string
     */
    public function getHeaderCssClass()
    {
        return 'head-additional-info';
    }

    /**
     * Return header text
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        return __('Additional Information');
    }

    /**
     * @return \Magento\Sales\Model\Order|mixed|null
     */
    public function getOrder()
    {
        return $this->_getSession()->getOrder();
    }

    /**
     * Get order custom fields
     *
     * @return \Digidirect\CheckoutFields\Model\ResourceModel\OrderFieldValue\Collection
     */
    public function getOrderCustomFields()
    {
        /**
         * @var $orderCustomFieldsModel \Digidirect\CheckoutFields\Model\OrderFieldValue
         */
        $orderCustomFieldsModel = $this->_orderCustomFields->create();
        return $orderCustomFieldsModel->getCustomFields($this->getOrder()->getId());
    }

    /**
     * Prepare Form and add elements to form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        $fieldset = $this->_form->addFieldset('main', []);
        $fields = (array)$this->parser->getFields($this->_sessionQuote->getStoreId());
        $this->sortFields($fields);
        foreach ($fields as $key => $field) {
            $model = ObjectManager::getInstance()
                ->create('\\Digidirect\\CheckoutFields\\Model\\Component\\Type\\' . ucfirst($field['frontend_input']));
            $type = $field['frontend_input'];
            if ($type == 'radio' && isset($field['options'])) {
                $type = 'radios';
            }
            if ($type == 'checkbox') {
                $this->checkboxElements[] = $key;
            }
            $fieldset->addField(
                $key,
                $type,
                $model->prepareFieldConfig($field, $key)
            );
        }
        $this->_form->addFieldNameSuffix('order[additional]');
        $this->_form->setValues($this->getFormValues());
        return $this;
    }

    /**
     * Return Form Elements values
     *
     * @return array
     */
    public function getFormValues()
    {
        $data = [];
        if ($this->getOrder()->getId()) {
            $customFields = $this->getOrderCustomFields();
            foreach ($customFields as $customField) {
                $data[$customField->getFieldId()] = unserialize($customField->getValue());
            }
            if ($this->checkboxElements) {
                foreach ($this->checkboxElements as $element) {
                    if (array_key_exists($element, $data)) {
                        $this->_form->getElement($element)->setIsChecked($data[$element]);
                    }
                }
            }
        }
        return $data;
    }

    /**
     * Sort fields by sort_order
     *
     * @param array $fields
     * @return mixed
     */
    public function sortFields(&$fields)
    {
        $sort = function ($a, $b) {
            $aPosition = $a['sort_order'] ?? self::DEFAULT_POSITION;
            $bPosition = $b['sort_order'] ?? self::DEFAULT_POSITION;
            return $aPosition <=> $bPosition;
        };

        uasort(
            $fields,
            $sort
        );
        return $fields;
    }
}
