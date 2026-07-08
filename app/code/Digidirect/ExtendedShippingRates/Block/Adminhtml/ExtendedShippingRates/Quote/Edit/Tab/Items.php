<?php
namespace Digidirect\ExtendedShippingRates\Block\Adminhtml\ExtendedShippingRates\Quote\Edit\Tab;

use Digidirect\ExtendedShippingRates\Ui\DataProvider\Quote\Form\QuoteDataProvider;

class Items extends \Magento\Backend\Block\Widget\Form\Generic implements
    \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * Core registry
     *
     * @var \Magento\Backend\Block\Widget\Form\Renderer\Fieldset
     */
    protected $rendererFieldset;

    /**
     * @var \Magento\Rule\Block\Actions
     */
    protected $actions;

    /**
     * @var \Digidirect\ExtendedShippingRates\Model\RuleFactory
     */
    private $ruleFactory;

    /**
     * @var \Digidirect\ExtendedShippingRates\Api\RuleRepositoryInterface
     */
    protected $ruleRepository;

    /**
     * @var string
     */
    protected $_nameInLayout = 'rule_items';

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Rule\Block\Actions $actions
     * @param \Digidirect\ExtendedShippingRates\Model\RuleFactory $ruleFactory
     * @param \Magento\Backend\Block\Widget\Form\Renderer\Fieldset $rendererFieldset
     * @param \Digidirect\ExtendedShippingRates\Api\RuleRepositoryInterface $ruleRepository
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Rule\Block\Actions $actions,
        \Digidirect\ExtendedShippingRates\Model\RuleFactory $ruleFactory,
        \Magento\Backend\Block\Widget\Form\Renderer\Fieldset $rendererFieldset,
        \Digidirect\ExtendedShippingRates\Api\RuleRepositoryInterface $ruleRepository,
        array $data = []
    ) {
        $this->rendererFieldset = $rendererFieldset;
        $this->actions = $actions;
        $this->ruleFactory = $ruleFactory;
        $this->ruleRepository = $ruleRepository;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Apply to Items');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Apply to Items');
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
     * Prepare form before rendering HTML
     *
     * @return \Magento\Backend\Block\Widget\Form\Generic
     */
    protected function _prepareForm()
    {
        /** @var \Digidirect\ExtendedShippingRates\Model\Rule $model */
        $model = $this->_coreRegistry->registry(\Digidirect\ExtendedShippingRates\Model\Rule::CURRENT_PROMO_QUOTE_RULE);

        if (!$model) {
            $id = $this->getRequest()->getParam('id');
            $model = $this->ruleRepository->getById($id);
        }

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('rule_');

        $renderer = $this->rendererFieldset->setTemplate(
            'Magento_CatalogRule::promo/fieldset.phtml'
        )->setNewChildUrl(
            $this->getUrl(
                'digidirect_extendedshippingrates/extendedshippingrates_quote/newActionHtml/form/rule_items_fieldset'
            )
        );

        $fieldset = $form->addFieldset(
            'items_fieldset',
            [
                'legend' => __(
                    'Apply the rule only to cart items matching the following conditions ' .
                    '(leave blank for all items).'
                )
            ]
        )->setRenderer(
            $renderer
        );

        $fieldset->addField(
            'items',
            'text',
            [
                'name' => 'apply_to',
                'label' => __('Apply To'),
                'title' => __('Apply To'),
                'data-form-part' => QuoteDataProvider::FORM_NAME
            ]
        )->setRule(
            $model
        )->setRenderer(
            $this->actions
        );

        $form->setValues($model->getData());
        $this->setConditionFormName($model->getActions(), QuoteDataProvider::FORM_NAME);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Handles addition of form name to condition and its conditions.
     *
     * @param \Magento\Rule\Model\Condition\AbstractCondition $conditions
     * @param string $formName
     * @return void
     */
    private function setConditionFormName(\Magento\Rule\Model\Condition\AbstractCondition $conditions, $formName)
    {
        $conditions->setFormName($formName);
        $conditions = $conditions->getConditions();
        if ($conditions && is_array($conditions)) {
            foreach ($conditions as $condition) {
                $this->setConditionFormName($condition, $formName);
            }
        }
    }
}
