<?php
namespace Digidirect\Utilities\Block\View\Element\Message\Renderer;

use Magento\Framework\Message\MessageInterface;
use Magento\Framework\View\Element\Message\Renderer\RendererInterface;
use Magento\Framework\View\Element\Message\Renderer\BlockRenderer\Template;
use Digidirect\Utilities\Block\View\Element\Message\Renderer\CustomizedMessageFactory;

/**
 * Class ExtendedRenderer
 * @package Digidirect\Utilities\Block\View\Element\Message\Renderer
 */
class ExtendedRenderer implements RendererInterface
{
    const CODE = 'extended_renderer';

    /**
     * @var Template
     */
    protected $_template;

    /**
     * @var array
     */
    protected $_configuration;

    /**
     * @var \Digidirect\Utilities\Helper\Message
     */
    protected $_messageHelper;

    /**
     * @var CustomizedMessageFactory
     */
    protected $_customizedMessageFactory;

    /**
     * @var string
     */
    protected $_textRendererTemplate;

    /**
     * ExtendedRenderer constructor.
     * @param Template $_template
     * @param \Digidirect\Utilities\Helper\Message $_messageHelper
     * @param CustomizedMessageFactory $customizedMessageFactory
     * @param string $textRendererTemplate
     */
    public function __construct(
        Template $_template,
        \Digidirect\Utilities\Helper\Message $_messageHelper,
        CustomizedMessageFactory $customizedMessageFactory,
        $textRendererTemplate = 'Digidirect_Utilities::messages/text.phtml'
    ) {
        $this->_messageHelper = $_messageHelper;
        $this->_template = $_template;
        $this->_customizedMessageFactory = $customizedMessageFactory;
        $this->_textRendererTemplate = $textRendererTemplate;
    }

    /**
     * Renders custom message
     *
     * @param MessageInterface $message
     * @param array $initializationData
     * @return string
     */
    public function render(MessageInterface $message, array $initializationData)
    {
        $this->_setUpConfiguration($message, $initializationData);

        $result = $this->_template->toHtml();

        $this->_tearDownConfiguration();

        return $result;
    }

    /**
     * @param string $text
     * @param string $template
     * @return string
     */
    public function renderText($text, $template = null)
    {
        if ($template === null) {
            $template = $this->_textRendererTemplate;
        }

        /** @var CustomizedMessage $message */
        $message = $this->_customizedMessageFactory->create();
        $message->setText($text);
        $message->setData(['origin_message' => $text]);
        $initializationData = [
            'template' => $template
        ];
        return $this->render($message, $initializationData);
    }

    /**
     * @param object $message
     * @param array $initializationData
     * @return void
     */
    protected function _setUpConfiguration($message, array $initializationData)
    {
        $data = $message->getData();
        $messageSettings = $this->_messageHelper->getConfigValue($data['origin_message']);

        if (!isset($initializationData['template']) && !$messageSettings['renderer_template']) {
            throw new \InvalidArgumentException('Template should be provided for the renderer.');
        }

        $configuration = [
            'text' => $message->getText(),
            'class_name' => $messageSettings['css_class'] ?? '',
            'area' => 'frontend'
        ];

        $this->_configuration = $configuration;
        $template = $messageSettings['renderer_template'] ?? $initializationData['template'];

        $this->_template->setTemplate($template);
        $this->_template->setData($configuration);
    }

    /**
     * @return void
     */
    protected function _tearDownConfiguration()
    {
        foreach (array_keys($this->_configuration) as $key) {
            $this->_template->unsetData($key);
            unset($this->_configuration[$key]);
        }
        $this->_template->setTemplate('');
    }
}
