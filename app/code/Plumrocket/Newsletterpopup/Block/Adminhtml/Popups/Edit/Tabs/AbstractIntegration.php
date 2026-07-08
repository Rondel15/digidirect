<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2018 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Block\Adminhtml\Popups\Edit\Tabs;

use Magento\Backend\Block\Widget\Form\Element\Dependence as ElementDependence;
use Plumrocket\Newsletterpopup\Block\Adminhtml\Popups\Edit\Renderer\InputTable as InputTableBlock;
use Plumrocket\Newsletterpopup\Block\Adminhtml\Popups\Edit\Renderer\Label as ExtendedLabel;
use Plumrocket\Newsletterpopup\Helper\Data as DataHelper;
use Plumrocket\Newsletterpopup\Model\Config\Source\SubscriptionMode;

/**
 * Class AbstractIntegration
 */
abstract class AbstractIntegration extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var \Magento\Config\Model\Config\Source\Yesno
     */
    private $sourceYesNo;

    /**
     * @var DataHelper
     */
    private $dataHelper;

    /**
     * @var \Plumrocket\Newsletterpopup\Model\IntegrationRepositoryInterface
     */
    private $integrationRepository;

    /**
     * @var \Plumrocket\Newsletterpopup\Model\ResourceModel\MailchimpList\CollectionFactory
     */
    private $listCollectionFactory;

    /**
     * @var \Plumrocket\Newsletterpopup\Model\Config\Source\SubscriptionMode
     */
    private $sourceSubscriptionMode;

    /**
     * @var null|bool
     */
    private $isEnabled;

    /**
     * @return string
     */
    abstract public function getIntegrationId();

    /**
     * @return string
     */
    abstract public function getIntegrationTitle();

    /**
     * AbstractIntegration constructor.
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Config\Model\Config\Source\Yesno $sourceYesNo
     * @param \Plumrocket\Newsletterpopup\Helper\Data $dataHelper
     * @param \Plumrocket\Newsletterpopup\Model\IntegrationRepositoryInterface $integrationRepository
     * @param \Plumrocket\Newsletterpopup\Model\ResourceModel\MailchimpList\CollectionFactory $listCollectionFactory
     * @param \Plumrocket\Newsletterpopup\Model\Config\Source\SubscriptionMode $sourceSubscriptionMode
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Config\Model\Config\Source\Yesno $sourceYesNo,
        \Plumrocket\Newsletterpopup\Helper\Data $dataHelper,
        \Plumrocket\Newsletterpopup\Model\IntegrationRepositoryInterface $integrationRepository,
        \Plumrocket\Newsletterpopup\Model\ResourceModel\MailchimpList\CollectionFactory $listCollectionFactory,
        \Plumrocket\Newsletterpopup\Model\Config\Source\SubscriptionMode $sourceSubscriptionMode,
        array $data = []
    ) {
        $this->sourceYesNo = $sourceYesNo;
        $this->dataHelper = $dataHelper;
        $this->integrationRepository = $integrationRepository;
        $this->listCollectionFactory = $listCollectionFactory;
        $this->sourceSubscriptionMode = $sourceSubscriptionMode;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        if (null === $this->isEnabled) {
            $model = $this->getIntegrationModel();
            $this->isEnabled = $model ? $model->isEnable() : false;
        }

        return $this->isEnabled;
    }

    /**
     * @return \Plumrocket\Newsletterpopup\Model\IntegrationInterface
     */
    public function getIntegrationModel()
    {
        return $this->integrationRepository->get($this->getIntegrationId());
    }

    /**
     * @return array
     */
    public function getAllLists()
    {
        return $this->getIntegrationModel()->getAllLists();
    }

    /**
     * @return null|\Plumrocket\Newsletterpopup\Model\Popup
     */
    public function getPopup()
    {
        return $this->_coreRegistry->registry('current_model');
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        if (! $this->isEnabled()) {
            return '';
        }

        return parent::_toHtml();
    }

    /**
     * @return \Magento\Backend\Block\Widget\Form\Generic
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('popup_');
        $this->addFieldset($form);
        $form->setValues($this->getPopup() ? $this->getPopup()->getData() : null);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * @param \Magento\Framework\Data\Form $form
     * @return \Magento\Framework\Data\Form\Element\Fieldset
     */
    protected function addFieldset(\Magento\Framework\Data\Form $form)
    {
        $fieldset = $form->addFieldset(
            $this->getIntegrationId() . '_fieldset',
            [
                'legend' => $this->getIntegrationTitle()
            ]
        );

        $this->addFieldNotice($fieldset, $this->getNoticeText(), false);
        $enable = $this->addFieldIntegrationEnable($fieldset);
        $mode = $this->addFieldIntegrationMode($fieldset);
        $list = $this->addFieldIntegrationLists($fieldset);

        $dependencyBlock = $this->getNewDependencyBlock()
            ->addFieldMap($mode->getHtmlId(), $mode->getName())
            ->addFieldMap($list->getHtmlId(), $list->getName())
            ->addFieldMap($enable->getHtmlId(), $enable->getName())
            ->addFieldDependence($mode->getName(), $enable->getName(), 1)
            ->addFieldDependence($list->getName(), $enable->getName(), 1);

        $this->setChild('form_after', $dependencyBlock);

        return $fieldset;
    }

    /**
     * @param \Magento\Framework\Data\Form\Element\Fieldset $fieldset
     * @param $text
     * @param null $forceVisibility
     * @return null|\Magento\Framework\Data\Form\Element\AbstractElement
     */
    protected function addFieldNotice(
        \Magento\Framework\Data\Form\Element\Fieldset $fieldset,
        $text,
        $forceVisibility = null
    ) {
        if (empty($text)) {
            return null;
        }

        $fieldset->addType('extended_label', ExtendedLabel::class);
        $fieldName = $this->getIntegrationId() . '_extended_label';
        $field = $fieldset->addField(
            $fieldName,
            'extended_label',
            [
                'hidden' => null !== $forceVisibility ? ! $forceVisibility : true
            ]
        );

        $this->getPopup()->setData($fieldName, $text);

        return $field;
    }

    /**
     * @param \Magento\Framework\Data\Form\Element\Fieldset $fieldset
     * @return \Magento\Framework\Data\Form\Element\AbstractElement
     */
    protected function addFieldIntegrationEnable(\Magento\Framework\Data\Form\Element\Fieldset $fieldset)
    {
        $elementId = sprintf('integration_enable[%s]', $this->getIntegrationId());
        $value = $this->getPopup()->getPreparedIntegrationEnable($this->getIntegrationId());
        $this->getPopup()->setData($elementId, $value);

        return $fieldset->addField(
            $elementId,
            'select',
            [
                'name' => $elementId,
                'class' => 'integration-enable',
                'label' => __('Enable'),
                'title' => __('Enable'),
                'values' => $this->sourceYesNo->toOptionArray(),
                'value' => '',
            ]
        );
    }

    /**
     * @param \Magento\Framework\Data\Form\Element\Fieldset $fieldset
     * @return \Magento\Framework\Data\Form\Element\AbstractElement
     */
    protected function addFieldIntegrationMode(\Magento\Framework\Data\Form\Element\Fieldset $fieldset)
    {
        $elementId = sprintf('integration_mode[%s]', $this->getIntegrationId());
        $value = $this->getPopup()->getPreparedIntegrationMode($this->getIntegrationId());
        $value = empty($value) ? SubscriptionMode::ALL_SELECTED_LIST : $value;
        $this->getPopup()->setData($elementId, $value);

        return $fieldset->addField(
            $elementId,
            'select',
            [
                'name' => $elementId,
                'label' => __('User Subscription Mode'),
                'class' => 'integration-mode',
                'values' => $this->getSubscriptionModeValues(),
                'note' => $this->getSubscriptionModeNoteText(),
                'disabled' => ! $this->isEnabled(),
            ]
        );
    }

    /**
     * @param \Magento\Framework\Data\Form\Element\Fieldset $fieldset
     * @return \Magento\Framework\Data\Form\Element\AbstractElement
     */
    protected function addFieldIntegrationLists(\Magento\Framework\Data\Form\Element\Fieldset $fieldset)
    {
        $elementId = sprintf('integration_list[%s]', $this->getIntegrationId());
        $element = $fieldset->addField(
            $elementId,
            'text',
            [
                'name' => $elementId,
                'label' => __('Contact Lists'),
                'note' => $this->getIntegrationListNoteText(),
                'disabled' => ! $this->isEnabled(),
            ]
        );
        $element->setRenderer($this->getNewInputTableBlock());
        $element->getRenderer()
            ->setContainerFieldId($elementId)
            ->setRowKey('name')
            ->setAdminFieldClassName('integration-list')
            ->addColumn(
                'enable',
                [
                    'header' => __('Enable'),
                    'index' => 'enable',
                    'type' => 'checkbox',
                    'value' => '1',
                    'width' => '5%',
                    'column_css_class' => 'list-enable-column',
                ]
            )->addColumn(
                'orig_label',
                [
                    'header' => __('List Name'),
                    'index' => 'orig_label',
                    'type' => 'label',
                    'width' => '40%',
                ]
            )->addColumn(
                'subscribers_count',
                [
                    'header' => __('Subscribers'),
                    'index' => 'subscribers_count',
                    'type' => 'label',
                    'width' => '15%',
                    'align' => 'center',
                ]
            )->addColumn(
                'label',
                [
                    'header' => __('Displayed List Name'),
                    'index' => 'label',
                    'type' => 'input',
                    'width' => '40%',
                ]
            )->addColumn(
                'sort_order',
                [
                    'header' => __('Sort Order'),
                    'index' => 'sort_order',
                    'type' => 'input',
                    'width' => '15%',
                ]
            )->setArray($this->getPreparedIntegrationLists());

        return $element;
    }

    /**
     * @return InputTableBlock
     */
    private function getNewInputTableBlock()
    {
        return $this->getLayout()->createBlock(InputTableBlock::class);
    }

    /**
     * @return ElementDependence
     */
    private function getNewDependencyBlock()
    {
        return $this->getLayout()->createBlock(ElementDependence::class);
    }

    /**
     * @return array
     */
    public function getSubscriptionModeValues()
    {
        return $this->sourceSubscriptionMode->toOptionHash();
    }

    /**
     * @return array
     */
    public function getPreparedIntegrationLists()
    {
        $result = [];

        if ($model = $this->getIntegrationModel()) {
            $savedLists = $this->getSavedLists();

            if ($model->canUseGeneralContactList()) {
                $generalName = DataHelper::DEFAULT_GENERAL_LIST_NAME;
                $result[$generalName] = $this->getPreparedListData(
                    $generalName,
                    $this->getLabelForGeneralList(),
                    $savedLists
                );
                $result[$generalName]['subscribers_count'] = '-';
            }

            foreach ($model->getAllLists() as $listId => $listName) {
                $result[$listId] = $this->getPreparedListData($listId, $listName, $savedLists);
                $result[$listId]['subscribers_count'] = $model->getListSubscribersCount($listId);
                $result[$listId]['id'] = $listId;
            }

            $deletedLists = $this->getDeletedLists($savedLists, $result);
            $result = array_merge($result, $deletedLists);
        }

        return $result;
    }

    /**
     * @param $savedLists
     * @param $alreadyAddedLists
     * @return array
     */
    private function getDeletedLists($savedLists, $alreadyAddedLists)
    {
        $result = [];

        foreach ($savedLists as $listId => $listData) {
            if (! isset($alreadyAddedLists[$listId])) {
                $listData['orig_label'] = sprintf('%s (Deleted)', $listData['label']);
                $listData['subscribers_count'] = '-';
                $result[$listId] = $listData;
            }
        }

        return $result;
    }

    /**
     * @return array
     */
    public function getSavedLists()
    {
        /** @var \Plumrocket\Newsletterpopup\Model\ResourceModel\MailchimpList\Collection $collection */
        $collection = $this->listCollectionFactory->create();
        $collection->addIntegrationAndPopupFilter(
            $this->getIntegrationModel()->getIntegrationId(),
            $this->getPopup()->getId()
        );

        $result = [];

        if ($collection->getSize()) {
            foreach ($collection as $item) {
                $result[$item->getData('name')] = $item->getData();
            }
        }

        return $result;
    }

    /**
     * @param $listId
     * @param $listName
     * @param $savedLists
     * @return array
     */
    public function getPreparedListData($listId, $listName, $savedLists)
    {
        $enable = $savedLists[$listId]['enable'] ?? '0';
        $label = isset($savedLists[$listId]['label'])
            ? (string)$savedLists[$listId]['label']
            : $listName;
        $sortOrder = $savedLists[$listId]['sort_order'] ?? '0';

        return [
            'enable' => $enable,
            'name' => $listId,
            'orig_label' => $listName,
            'label' => $label,
            'sort_order' => $sortOrder,
        ];
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getNoticeText()
    {
        return __(
            'This newsletter subscription will not work until you enable at least one Contact List in the grid below.'
        );
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getSubscriptionModeNoteText()
    {
        return __(
            'Here you can allow users to subscribe to the list of their choice'
            . ' or automatically subscribe each new user to all Lists'
        );
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getIntegrationListNoteText()
    {
        return __('Please select Contact Lists where all subscribers should be added to.');
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getLabelForGeneralList()
    {
        return __('General Contact List');
    }
}
