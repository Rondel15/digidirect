<?php

namespace Digidirect\Feed\Block\Adminhtml\Template\Edit\Tab\Schema;

use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Block\Widget\Form;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Magento\Config\Model\Config\Source\Yesno as SourceYesNo;
use Digidirect\Feed\Model\Config\Source\Delimiter as SourceDelimiter;
use Digidirect\Feed\Model\Config\Source\Enclosure as SourceEnclosure;

class Csv extends Form
{
    /**
     * @var FormFactory
     */
    protected $formFactory;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var SourceYesNo
     */
    protected $sourceYesNo;

    /**
     * @var SourceDelimiter
     */
    protected $sourceDelimiter;

    /**
     * @var SourceEnclosure
     */
    protected $sourceEnclosure;

    /**
     * Csv constructor.
     * @param Context $context
     * @param FormFactory $formFactory
     * @param Registry $registry
     * @param SourceYesNo $sourceYesNo
     * @param SourceDelimiter $sourceDelimiter
     * @param SourceEnclosure $sourceEnclosure
     * @param array $data
     */
    public function __construct(
        Context $context,
        FormFactory $formFactory,
        Registry $registry,
        SourceYesNo $sourceYesNo,
        SourceDelimiter $sourceDelimiter,
        SourceEnclosure $sourceEnclosure,
        array $data = []
    ) {
        $this->formFactory = $formFactory;
        $this->registry = $registry;
        $this->sourceYesNo = $sourceYesNo;
        $this->sourceDelimiter = $sourceDelimiter;
        $this->sourceEnclosure = $sourceEnclosure;
        $this->_template = 'Digidirect_Feed::template/edit/tab/schema/csv.phtml';

        parent::__construct($context, $data);
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareForm()
    {
        $model = $this->getModel();
        $form = $this->formFactory->create();

        $this->setForm($form);

        $general = $form->addFieldset('content', ['legend' => __('Content Settings')]);

        $general->addField('delimiter', 'select', [
            'label' => __('Fields Delimiter'),
            'name' => 'csv[delimiter]',
            'value' => $model->getData('csv_delimiter'),
            'values' => $this->sourceDelimiter->toOptionArray(),
        ]);

        $general->addField('enclosure', 'select', [
            'label' => __('Fields enclosure'),
            'name' => 'csv[enclosure]',
            'value' => $model->getData('csv_enclosure'),
            'values' => $this->sourceEnclosure->toOptionArray(),
        ]);

        $general->addField('include_header', 'select', [
            'label' => __('Include Columns Header'),
            'name' => 'csv[include_header]',
            'value' => $model->getData('csv_include_header'),
            'values' => $this->sourceYesNo->toArray(),
        ]);

        $general->addField('extra_header', 'textarea', [
            'label' => __('Extra Header'),
            'required' => false,
            'name' => 'csv[extra_header]',
            'value' => $model->getData('csv_extra_header'),
        ]);

        return parent::_prepareForm();
    }

    /**
     * Return current template or feed model
     *
     * @return \Digidirect\Feed\Model\AbstractTemplate
     */
    public function getModel()
    {
        return $this->registry->registry('current_model');
    }

    /**
     * @return array
     */
    public function getJsConfig()
    {
        return [
            "*" => [
                'Magento_Ui/js/core/app' => [
                    'components' => [
                        'schema_csv' => [
                            'component' => 'Digidirect_Feed/js/template/edit/tab/schema/csv',
                            'config' => [
                                'rows' => $this->getModel()->getCsvSchema(),
                            ]
                        ]
                    ],
                ]
            ]
        ];
    }
}
