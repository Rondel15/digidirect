<?php

namespace Digidirect\ExtendedCartPriceRules\Block\Adminhtml\Widget\Form\Renderer;

use Magento\Backend\Block\Template\Context;

class Fieldset extends \Magento\Backend\Block\Widget\Form\Renderer\Fieldset
{
    /**
     * @var array
     */
    protected $rewriteTemplatesMap;

    /**
     * Fieldset constructor.
     * @param array $rewriteTemplatesMap
     * @param Context $context
     * @param array $data
     */
    public function __construct(array $rewriteTemplatesMap, Context $context, array $data = [])
    {
        $this->rewriteTemplatesMap = $rewriteTemplatesMap;
        parent::__construct($context, $data);
    }

    /**
     * @param string $template
     * @return \Magento\Backend\Block\Widget\Form\Renderer\Fieldset
     */
    public function setTemplate($template)
    {
        if (isset($this->rewriteTemplatesMap[$template])) {
            $template = $this->rewriteTemplatesMap[$template];
        }
        return parent::setTemplate($template);
    }
}
