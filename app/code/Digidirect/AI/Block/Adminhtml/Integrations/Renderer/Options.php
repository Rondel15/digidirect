<?php
namespace Digidirect\AI\Block\Adminhtml\Integrations\Renderer;

/**
 * Class Options
 * @package Digidirect\AI\Block\Adminhtml\Integrations\Renderer
 */
class Options extends \Magento\Framework\View\Element\Template
{
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager = null;

    /**
     * @var array
     */
    protected $options = [];

    /**
     * Options constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        array $data = []
    ) {
        $this->_objectManager = $objectManager;
        $this->setTemplate('integrations/renderer/options.phtml');
        parent::__construct($context, $data);
    }

    /**
     * @param mixed $options
     * @return $this
     */
    public function setOptions($options)
    {
        $this->options = $options;
        return $this;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param string $code
     * @return mixed
     */
    public function getSelectOptions($code)
    {
        return $this->_objectManager->get($code)->toOptionArray();
    }
}
