<?php
namespace Digidirect\Collect\Block\Adminhtml\System\Config;

/**
 * Class ImportFile
 * @package Collect\Block\Adminhtml\System\Config
 */
class ImportFile extends \Magento\Config\Block\System\Config\Form\Field
{

    const DEFAULT_STORE_ID = 0;

    const TEMPLATE_PATH = 'system/config/import-file-button.phtml';
    const URL_PATH = 'digidirectcollect/index/import/';

    /**
     * @var string
     */
    protected $buttonLabel = 'Import';

    /**
     * ImportFile constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * Set template to itself
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (!$this->getTemplate()) {
            $this->setTemplate(self::TEMPLATE_PATH);
        }
        return $this;
    }

    /**
     * Get the button and scripts contents
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $originalData = $element->getOriginalData();
        $buttonLabel = !empty($originalData['button_label']) ? $originalData['button_label'] : $this->buttonLabel;
        $storeId = $this->getRequest()->getParam('store');
        $websiteId = $this->getRequest()->getParam('website');
        if ($storeId) {
            $params = 'store_id/' . $storeId;
        } elseif ($websiteId) {
            $params = 'website_id/' . $websiteId;
        } else {
            $params = 'store_id/' . self::DEFAULT_STORE_ID;
        }

        $this->addData(
            [
                'button_label' => __($buttonLabel),
                'disabled' => false,
                'html_id' => $element->getHtmlId(),
                'import_url' => $this->_urlBuilder->getUrl(
                    self::URL_PATH . $params
                )
            ]
        );

        return $this->_toHtml();
    }

    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _renderInheritCheckbox(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        return '';
    }

    /**
     * Render scope label
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _renderScopeLabel(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        return '';
    }
}
