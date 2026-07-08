<?php

namespace Digidirect\Feed\Block\Adminhtml\Template\Edit\Tab\Schema;

use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Block\Widget\Form;
use Magento\Framework\Registry;
use Digidirect\Feed\Helper\Output as OutputHelper;

class Xml extends Form
{
    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var OutputHelper
     */
    protected $outputHelper;

    /**
     * Xml constructor.
     * @param Context $context
     * @param Registry $registry
     * @param OutputHelper $outputHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        OutputHelper $outputHelper,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->outputHelper = $outputHelper;

        $this->_template = 'Digidirect_Feed::template/edit/tab/schema/xml.phtml';

        parent::__construct($context, $data);
    }

    /**
     * Current template or feed model
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
                        'schema_xml' => [
                            'component' => 'Digidirect_Feed/js/template/edit/tab/schema/xml',
                            'config' => [
                                'liquidTemplate' => $this->getModel()->getLiquidTemplate(),
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }
}
