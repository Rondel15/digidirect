<?php

namespace Digidirect\CheckoutFields\Block\Adminhtml\Order\View\Tab;

use Digidirect\CheckoutFields\Helper\Config;
use Magento\Backend\Block\Template as BackendTemplate;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Backend\Block\Template\Context as TemplateContext;
use Magento\Framework\Registry;
use Digidirect\CheckoutFields\Model\OrderFieldValueFactory;

/**
 * Class AdditionalFields
 *
 * @package Digidirect\CheckoutFields\Block\Adminhtml\Order\View\Tab
 */
class AdditionalFields extends BackendTemplate implements TabInterface
{
    /**
     * Template
     *
     * @var string
     */
    protected $_template = 'order/view/tab/additional_fields.phtml';

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var OrderFieldValueFactory
     */
    protected $_orderCustomFields;

    /**
     * @var \Digidirect\CheckoutFields\Helper\Xml\Fields\Parser
     */
    protected $parser;

    /**
     * @var OrderFieldValueFactory
     */
    protected $fieldValueFactory;

    /**
     * @var Config
     */
    protected $configHelper;

    /**
     * AdditionalFields constructor.
     *
     * @param TemplateContext $context
     * @param Registry $registry
     * @param OrderFieldValueFactory $orderFieldValue
     * @param \Digidirect\CheckoutFields\Helper\Xml\Fields\Parser $parser
     * @param OrderFieldValueFactory $fieldValueFactory
     * @param Config $configHelper
     * @param array $data
     */
    public function __construct(
        TemplateContext $context,
        Registry $registry,
        OrderFieldValueFactory $orderFieldValue,
        \Digidirect\CheckoutFields\Helper\Xml\Fields\Parser $parser,
        \Digidirect\CheckoutFields\Model\OrderFieldValueFactory $fieldValueFactory,
        Config $configHelper,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->_orderCustomFields = $orderFieldValue;
        parent::__construct($context, $data);
        $this->parser = $parser;
        $this->fieldValueFactory = $fieldValueFactory;
        $this->configHelper = $configHelper;
    }

    /**
     * Retrieve order model instance
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->_coreRegistry->registry('current_order');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Additional Information');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Additional Information');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Get order custom fields
     *
     * @return array
     */
    public function getOrderCustomFields()
    {
        /**
         * @var $orderCustomFieldsModel \Digidirect\CheckoutFields\Model\OrderFieldValue
         */
        $orderCustomFieldsModel = $this->_orderCustomFields->create();
        $orderFields = $orderCustomFieldsModel->getCustomFields($this->getOrder()->getId());
        $resultFields = [];
        foreach ($this->parser->getFields() as $key => $field) {
            $found = null;
            foreach ($orderFields as $orderField) {
                if ($orderField->getFieldId() === $key) {
                    $found = $orderField;
                }
            }
            if (!$found) {
                $found = $this->fieldValueFactory->create([
                    'data' => [
                        'field_id' => $key,
                        'code' => $field['frontend_name'],
                    ],
                ]);
            }
            $found->setData(
                'html_classes',
                $this->parser->getValidationClassesHtml($field)
            );
            $resultFields[] = $found;
        }
        return $resultFields;
    }

    /**
     * Unserialize and return value
     *
     * @param string $value
     * @return string
     */
    public function getValue($value)
    {
        if(is_null($value))
        {
            return $value;
        }
        $value = unserialize($value);
        return is_array($value) ? implode(', ', $value) : $value;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function allowEdit($key)
    {
        return $this->configHelper->isAllowEditOnODP($key, $this->getOrder()->getStoreId());
    }
}
