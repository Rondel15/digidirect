<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2018 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Controller\Adminhtml\Popups;

use Magento\PageCache\Model\Cache\Type as PageCache;

class Save extends \Plumrocket\Newsletterpopup\Controller\Adminhtml\Popups
{
    /**
     * @var \Plumrocket\Newsletterpopup\Model\FormFieldFactory
     */
    private $_formFieldFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\Filter\DateFactory
     */
    private $_dateFilterFactory;

    /**
     * @var \Plumrocket\Newsletterpopup\Model\ResourceModel\MailchimpList\CollectionFactory
     */
    private $listCollectionFactory;

    /**
     * @var \Plumrocket\Newsletterpopup\Model\ResourceModel\MailchimpList
     */
    private $listResource;

    /**
     * @var \Plumrocket\Newsletterpopup\Model\Popup\Thumbnail\Generator
     */
    private $thumbnailGenerator;

    /**
     * @var \Magento\Framework\App\Cache\TypeListInterface
     */
    private $cacheTypeList;

    /**
     * @var \Plumrocket\Newsletterpopup\Helper\Data
     */
    private $dataHelper;

    /**
     * @param \Magento\Backend\App\Action\Context                                             $context
     * @param \Plumrocket\Newsletterpopup\Helper\Data                                         $dataHelper
     * @param \Magento\Framework\App\ResourceConnection                                       $resource
     * @param \Plumrocket\Newsletterpopup\Model\FormFieldFactory                              $formFieldFactory
     * @param \Magento\Framework\Stdlib\DateTime\Filter\DateFactory                           $dateFilterFactory
     * @param \Plumrocket\Newsletterpopup\Model\ResourceModel\MailchimpList\CollectionFactory $listCollectionFactory
     * @param \Plumrocket\Newsletterpopup\Model\ResourceModel\MailchimpList                   $listResource
     * @param \Plumrocket\Newsletterpopup\Model\Popup\Thumbnail\Generator                     $thumbnailGenerator
     * @param \Magento\Framework\App\Cache\TypeListInterface                                  $cacheTypeList
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Plumrocket\Newsletterpopup\Helper\Data $dataHelper,
        \Magento\Framework\App\ResourceConnection $resource,
        \Plumrocket\Newsletterpopup\Model\FormFieldFactory $formFieldFactory,
        \Magento\Framework\Stdlib\DateTime\Filter\DateFactory $dateFilterFactory,
        \Plumrocket\Newsletterpopup\Model\ResourceModel\MailchimpList\CollectionFactory $listCollectionFactory,
        \Plumrocket\Newsletterpopup\Model\ResourceModel\MailchimpList $listResource,
        \Plumrocket\Newsletterpopup\Model\Popup\Thumbnail\Generator $thumbnailGenerator,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
    ) {
        $this->_formFieldFactory = $formFieldFactory;
        $this->_dateFilterFactory = $dateFilterFactory;
        $this->listCollectionFactory = $listCollectionFactory;
        $this->listResource = $listResource;
        parent::__construct($context, $dataHelper, $resource);
        $this->thumbnailGenerator = $thumbnailGenerator;
        $this->cacheTypeList = $cacheTypeList;
        $this->dataHelper = $dataHelper;
    }

    /**
     * @param \Magento\Rule\Model\AbstractModel $model
     * @param \Magento\Framework\App\RequestInterface $request
     * @return void
     */
    protected function _beforeSave($model, $request)
    {
        $data = $request->getParams();
        $data = $this->_filterPostData($data);
        $model->loadPost($data);
    }

    /**
     * @param \Magento\Rule\Model\AbstractModel $model
     * @param \Magento\Framework\App\RequestInterface $request
     * @return void
     */
    protected function _afterSave($model, $request)
    {
        $this->cacheTypeList->invalidate(PageCache::TYPE_IDENTIFIER);
        $model->cleanCache();

        if ($id = (int) $model->getId()) {
            if ($fieldsData = $request->getParam('signup_fields')) {
                $this->_saveFormFields($fieldsData, $id);
            }

            $this->saveIntegrationList($request->getParams(), $id);
            $this->thumbnailGenerator->generate($id);
        }
    }

    /**
     * Prepare extended time fields that was passing in POST data
     *
     * @param $postData
     * @param $fieldName
     * @return array
     */
    protected function _prepareExtendedTime($postData, $fieldName)
    {
        if (isset($postData[$fieldName]) && is_array($postData[$fieldName])) {
            $offset = $this->dataHelper->getOffsetFromExtendedTime($postData[$fieldName], $fieldName);
            $postData[$fieldName] = !$offset ? null : implode(',', $postData[$fieldName]);
        }

        return $postData;
    }

    /**
     * @param $postData
     * @return array
     */
    protected function _prepareIntegrationMode($postData)
    {
        if (empty($postData['integration_enable'])) {
            $postData['integration_enable'] = [];
        }

        if (empty($postData['integration_mode'])) {
            $postData['integration_mode'] = [];
        }

        $postData['integration_enable'] = json_encode($postData['integration_enable']);
        $postData['integration_mode'] = json_encode($postData['integration_mode']);

        return $postData;
    }

    /**
     * Filtering posted data. Converting localized data if needed
     *
     * @param array
     * @return array
     */
    protected function _filterPostData($postData)
    {
        $postData = $this->_prepareExtendedTime($postData, 'cookie_time_frame');
        $postData = $this->_prepareExtendedTime($postData, 'coupon_expiration_time');
        $postData = $this->_prepareIntegrationMode($postData);

        if (isset($postData['stores'])) {
            if (in_array(0, $postData['stores'])) {
                $postData['store_id'] = '0';
            } else {
                $postData['store_id'] = implode(',', $postData['stores']);
            }
        }

        if (isset($postData['entity_id']) && empty($postData['entity_id'])) {
            unset($postData['entity_id']);
        }

        // Prepare dates.
        if (!empty($postData['start_date'])) {
            $inputFilter = $this->createFilterInput(
                ['start_date' => $this->_dateFilterFactory->create()],
                [],
                $postData
            );
            $postData = $inputFilter->getUnescaped();
        }

        if (!empty($postData['end_date'])) {
            $inputFilter = $this->createFilterInput(
                ['end_date' => $this->_dateFilterFactory->create()],
                [],
                $postData
            );
            $postData = $inputFilter->getUnescaped();
        }

        if (!isset($postData['code']) && !empty($postData['code_base64'])) {
            $postData['code'] = base64_decode($postData['code_base64']);
        }
        if (!isset($postData['style']) && !empty($postData['style_base64'])) {
            $postData['style'] = base64_decode($postData['style_base64']);
        }

        if (isset($postData['rule']['conditions'])) {
            $postData['conditions'] = $postData['rule']['conditions'];
        }
        if (isset($postData['rule']['actions'])) {
            $postData['actions'] = $postData['rule']['actions'];
        }
        unset($postData['rule']);

        return $postData;
    }

    /**
     * @param $data
     * @param $popupId
     * @return bool
     */
    protected function _saveFormFields($data, $popupId)
    {
        if (!$popupId) {
            return false;
        }
        // Email is require field
        if (isset($data['email'])) {
            $data['email']['enable'] = 1;
        }
        // If Confirmation is enabled but Password not then enable Password
        if (isset($data['confirm_password'])
            && isset($data['confirm_password']['enable'])
            && isset($data['password'])
            && !isset($data['password']['enable'])
        ) {
            $data['password']['enable'] = 1;
        }

        $systemItemsKeys = $this->dataHelper->getPopupFormFieldsKeys(0, false);
        $popupItems = $this->dataHelper->getPopupFormFields($popupId, false);

        foreach ($systemItemsKeys as $name) {
            if (array_key_exists($name, $data)) {
                if (array_key_exists($name, $popupItems)) {
                    $field = $popupItems[$name];
                } else {
                    $field = $this->_formFieldFactory->create();
                    $field->setData('popup_id', $popupId);
                    $field->setData('name', $name);
                }
                $field->setData('label', $data[$name]['label']);
                $field->setData('enable', (int)isset($data[$name]['enable']));
                $field->setData('sort_order', (int)$data[$name]['sort_order']);
                $field->save();
            }
        }
        return true;
    }

    /**
     * @param $postData
     * @param $popupId
     * @return $this
     */
    private function saveIntegrationList($postData, $popupId)
    {
        if (! empty($postData['mailchimp_list'])) {
            $postData['integration_list']['mailchimp'] = is_array($postData['mailchimp_list'])
                ? $postData['mailchimp_list']
                : [];
        }

        if (! empty($postData['integration_list']) && is_array($postData['integration_list'])) {
            /** @var \Plumrocket\Newsletterpopup\Model\ResourceModel\MailchimpList\Collection $collection */
            $collection = $this->listCollectionFactory->create();
            $collection->addPopupFilter($popupId);
            $savedLists = $collection->getGroupedIntegrationLists();
            $insertAndUpdateData = [];

            foreach ($postData['integration_list'] as $integrationId => $postLists) {
                if (! is_array($postLists)) {
                    continue;
                }

                $insertAndUpdateData = array_merge(
                    $insertAndUpdateData,
                    $this->prepareInsertAndUpdateDataForIntegration($integrationId, $popupId, $postLists, $savedLists)
                );
            }

            $this->listResource->insertAndUpdateLists($insertAndUpdateData);
        }

        return $this;
    }

    /**
     * @param $integrationId
     * @param $popupId
     * @param array $postLists
     * @param array $savedLists
     * @return array
     */
    private function prepareInsertAndUpdateDataForIntegration(
        $integrationId,
        $popupId,
        array $postLists,
        array $savedLists
    ) {
        $insertAndUpdateData = [];

        foreach ($postLists as $listId => $postList) {
            $label = ! empty($postList['label']) ? (string)$postList['label'] : $listId;
            $enable = isset($postList['enable']) ? 1 : 0;
            $sortOrder = isset($postList['sort_order']) ? (int)$postList['sort_order'] : 0;
            $insertAndUpdateItemData = [
                'entity_id' => null,
                'popup_id' => $popupId,
                'integration_id' => $integrationId,
                'name' => (string)$listId,
                'label' => $label,
                'enable' => $enable,
                'sort_order' => $sortOrder,
            ];

            if (! empty($savedLists[$integrationId][$listId])) {
                $insertAndUpdateItemData['entity_id'] = $savedLists[$integrationId][$listId]['entity_id'];
            }

            $insertAndUpdateData[] = $insertAndUpdateItemData;
        }

        return $insertAndUpdateData;
    }

    /**
     * Fix for Magento older than 2.4.6
     *
     * @param       $filterRules
     * @param       $validatorRules
     * @param array $data
     * @return \Magento\Framework\Filter\FilterInput|\Zend_Filter_Input
     */
    private function createFilterInput($filterRules, $validatorRules, array $data)
    {
        if (class_exists(\Magento\Framework\Filter\FilterInput::class)) {
            return new \Magento\Framework\Filter\FilterInput(
                $filterRules,
                $validatorRules,
                $data
            );
        }

        $zendFilterClassName = 'Zend_Filter'.'_Input';

        return new $zendFilterClassName($filterRules, $validatorRules, $data);
    }
}
