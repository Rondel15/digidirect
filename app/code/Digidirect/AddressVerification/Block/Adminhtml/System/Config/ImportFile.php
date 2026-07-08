<?php
namespace Digidirect\AddressVerification\Block\Adminhtml\System\Config;

use Digidirect\AddressVerification\Api\LocationRepositoryInterface;
use Digidirect\AddressVerification\Helper\Autocomplete;
use Digidirect\AddressVerification\Model\Config\Source\Type;
use Magento\Framework\DataObject;
use Magento\Store\Model\ScopeInterface;

/**
 * Class ImportFile
 * @package AddressVerification\Block\Adminhtml\System\Config
 */
class ImportFile extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @var string
     */
    protected $buttonLabel = 'Import';

    /**
     * @var \Digidirect\AddressVerification\Api\ImportReportRepositoryInterface
     */
    protected $reportRepository;

    /**
     * @var \Digidirect\AddressVerification\Helper\Aupost
     */
    protected $aupostHelper;

    /**
     * @var Autocomplete
     */
    protected $autocompleteHelper;

    /**
     * ImportFile constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Digidirect\AddressVerification\Api\ImportReportRepositoryInterface $reportRepository
     * @param \Digidirect\AddressVerification\Helper\Aupost $aupostHelper
     * @param Autocomplete $autocompleteHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Digidirect\AddressVerification\Api\ImportReportRepositoryInterface $reportRepository,
        \Digidirect\AddressVerification\Helper\Aupost $aupostHelper,
        Autocomplete $autocompleteHelper,
        array $data = []
    ) {
        $this->reportRepository = $reportRepository;
        $this->aupostHelper = $aupostHelper;
        $this->autocompleteHelper = $autocompleteHelper;
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
            $this->setTemplate('system/config/imort-file-button.phtml');
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
            $params = 'store_id/' . LocationRepositoryInterface::DEFAULT_STORE_ID;
        }

        $this->addData(
            [
                'button_label' => __($buttonLabel),
                'disabled' => $this->isDisabled(),
                'html_id' => $element->getHtmlId(),
                'import_url' => $this->_urlBuilder->getUrl(
                    'digidirect_addressverification/index/import/' . $params
                )
            ]
        );

        return $this->_toHtml();
    }

    /**
     * @return bool
     */
    protected function isDisabled()
    {
        if(empty($this->hasData('file_element'))) {
            
            return false;
        }
        
        return $this->isElementInherit() || !$this->hasFile();
    }

    /**
     * @return DataObject
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function extractFileData()
    {
        if (!$this->hasData('file_element')) {
            $this->setData('file_element', (new DataObject()));
            /** @var \Magento\Config\Block\System\Config\Edit $edit */
            $edit = $this->getLayout()->getBlock('system.config.edit');
            /** @var \Magento\Config\Block\System\Config\Form $form */
            $form = $edit->getChildBlock('form');
            /** @var \Magento\Framework\Data\Form\Element\Fieldset $fieldSet */
            $fieldSet = $form->getForm()->getElement('digidirect_address_suggestion_general');
            /** @var \Magento\Config\Block\System\Config\Form\Field\File $element */
            foreach ($fieldSet->getElements() as $element) {
                if ($element->getId() == 'digidirect_address_suggestion_general_address_data_file') {
                    $this->setData('file_element', $element);
                    break;
                }
            }
        }
        return $this->getData('file_element');
    }

    /**
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function isElementInherit()
    {
        return $this->extractFileData()->getData('inherit');
    }

    /**
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function hasFile()
    {
        return $this->extractFileData()->getData('value');
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

    /**
     * @return mixed
     */
    public function getLastImportTime()
    {
        if (!$this->hasData('last_report_time')) {
            $storeId = $this->getRequest()->getParam('store');
            $websiteId = $this->getRequest()->getParam('website');
            if (!$storeId && !$websiteId) {
                $storeId = LocationRepositoryInterface::DEFAULT_STORE_ID;
            }

            if ($this->isElementInherit() && !$websiteId) {
                foreach ($this->_storeManager->getStores() as $store) {
                    if ($store->getId() == $storeId) {
                        $websiteId = $store->getWebsiteId();
                    }
                }
            }
            $scopeInfo = $this->autocompleteHelper->getScopeInfo($storeId, $websiteId);
            $time = $this->reportRepository->getLastImportTime(
                $this->aupostHelper->getCurrentConfigCountry($scopeInfo->getScopeType(), $scopeInfo->getScopeId()),
                Type::AU_POST,
                $storeId,
                $websiteId,
                $this->isElementInherit()
            );
            $this->setData(
                'last_report_time',
                $time ? $this->aupostHelper->formatDate($time) : null
            );
        }
        return $this->getData('last_report_time');
    }
}
