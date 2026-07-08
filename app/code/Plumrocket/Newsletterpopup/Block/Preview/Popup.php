<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Block\Preview;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\ImageFactory;
use Magento\Cms\Model\Template\FilterProvider;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;
use Plumrocket\Newsletterpopup\Api\Data\PopupFieldDataInterface;
use Plumrocket\Newsletterpopup\Block\Popup as PopupBase;
use Plumrocket\Newsletterpopup\Helper\Data;
use Plumrocket\Newsletterpopup\Model\Config\Source\MailchimpList;
use Plumrocket\Newsletterpopup\Model\Popup\GetCurrentByRequest;
use Plumrocket\Newsletterpopup\Model\Popup\Variable;
use Plumrocket\Newsletterpopup\Model\Popup\Variable\Placeholder;
use Plumrocket\Newsletterpopup\Model\PopupFactory;
use Plumrocket\Newsletterpopup\ViewModel\Popup\Product as PopupProduct;
use Plumrocket\Newsletterpopup\ViewModel\Popup\Renderer;

class Popup extends PopupBase
{
    private $_objectManager;
    private $_sourceMailchimpList;

    private $_popup = null;

    /**
     * @var \Plumrocket\Newsletterpopup\Model\Popup\GetFields
     */
    private $getFields;

    /**
     * @var \Plumrocket\Newsletterpopup\Api\Data\PopupFieldDataInterfaceFactory
     */
    private $fieldFactory;

    /**
     * @param \Magento\Framework\View\Element\Template\Context                    $context
     * @param \Plumrocket\Newsletterpopup\Helper\Data                             $dataHelper
     * @param \Magento\Framework\ObjectManagerInterface                           $objectManager
     * @param \Plumrocket\Newsletterpopup\Model\Config\Source\MailchimpList       $sourceMailchimpList
     * @param \Magento\Cms\Model\Template\FilterProvider                          $filterProvider
     * @param \Magento\Catalog\Api\ProductRepositoryInterface                     $productRepository
     * @param \Magento\Framework\Registry                                         $coreRegistry
     * @param \Magento\Catalog\Helper\ImageFactory                                $imageHelperFactory
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface                   $priceCurrency
     * @param \Plumrocket\Newsletterpopup\Model\Popup\Variable                    $templateVariables
     * @param \Plumrocket\Newsletterpopup\ViewModel\Popup\Product                 $popupProduct
     * @param \Plumrocket\Newsletterpopup\Model\Popup\Variable\Placeholder        $variablePlaceholder
     * @param \Plumrocket\Newsletterpopup\Model\Popup\GetCurrentByRequest         $getCurrentPopup
     * @param \Plumrocket\Newsletterpopup\Model\PopupFactory                      $popupFactory
     * @param \Plumrocket\Newsletterpopup\ViewModel\Popup\Renderer                $renderer
     * @param \Plumrocket\Newsletterpopup\Model\Popup\GetFields                   $getFields
     * @param \Plumrocket\Newsletterpopup\Api\Data\PopupFieldDataInterfaceFactory $fieldFactory
     * @param array                                                               $data
     */
    public function __construct(
        Context $context,
        Data $dataHelper,
        ObjectManagerInterface $objectManager,
        MailchimpList $sourceMailchimpList,
        FilterProvider $filterProvider,
        ProductRepositoryInterface $productRepository,
        Registry $coreRegistry,
        ImageFactory $imageHelperFactory,
        PriceCurrencyInterface $priceCurrency,
        Variable $templateVariables,
        PopupProduct $popupProduct,
        Placeholder $variablePlaceholder,
        GetCurrentByRequest $getCurrentPopup,
        PopupFactory $popupFactory,
        Renderer $renderer,
        \Plumrocket\Newsletterpopup\Model\Popup\GetFields $getFields,
        \Plumrocket\Newsletterpopup\Api\Data\PopupFieldDataInterfaceFactory $fieldFactory,
        array $data = []
    ) {
        $this->_objectManager = $objectManager;
        $this->_sourceMailchimpList = $sourceMailchimpList;
        parent::__construct(
            $context,
            $dataHelper,
            $filterProvider,
            $productRepository,
            $coreRegistry,
            $imageHelperFactory,
            $priceCurrency,
            $templateVariables,
            $popupProduct,
            $variablePlaceholder,
            $getCurrentPopup,
            $popupFactory,
            $renderer,
            $data
        );
        $this->getFields = $getFields;
        $this->fieldFactory = $fieldFactory;
    }

    /**
     * Get popup with preview data.
     *
     * @return \Plumrocket\Newsletterpopup\Api\Data\PopupInterface
     */
    public function getPopup()
    {
        if (null === $this->_popup) {
            $request = $this->getRequest();

            $id = (int)$request->getParam('id');
            if (!$id) {
                $id = (int)$request->getParam('entity_id');
            }

            if ($request->getParam('is_template')) {
                $this->_popup = $this->_dataHelper->getPopupTemplateById($id);
            } else {
                $this->_popup = $this->_dataHelper->getPopupById($id);
            }

            $data = $request->getParams();
            if (!isset($data['code']) && !empty($data['code_base64'])) {
                $request->setParam('code', base64_decode($data['code_base64']));
            }
            if (!isset($data['style']) && !empty($data['style_base64'])) {
                $request->setParam('style', base64_decode($data['style_base64']));
            }

            $fields = [
                'animation',
                'text_title',
                'signup_fields',
                'subscription_mode',
                'mailchimp_list',
                'text_description',
                'text_success',
                'text_submit',
                'text_cancel',

                'name',
                'code',
                'style',
            ];

            foreach ($fields as $field) {
                $val = $request->getParam($field);
                if ($val) {
                    if ($field == 'mailchimp_list') {
                        $this->_popup->setData('custom_' . $field, $this->_loadMailChimpList($val));
                    } elseif ($field == 'signup_fields') {
                        $this->_popup->setData('custom_' . $field, $this->createFormFields($val));
                    } else {
                        $this->_popup->setData($field, $val);
                    }
                }
            }
        }

        return $this->_popup;
    }

    /**
     * Create form fields from params.
     *
     * @param array $params
     * @return array
     */
    protected function createFormFields(array $params): array
    {
        $result = [];
        foreach ($this->getFields->default() as $field) {
            $fieldName = $field->getName();
            if (! array_key_exists($fieldName, $params) || ! isset($params[$fieldName]['enable'])) {
                continue;
            }
            /* @var PopupFieldDataInterface|\Plumrocket\Newsletterpopup\Model\FormField $emptyField */
            $emptyField = $this->fieldFactory->create();
            $emptyField
                ->setPopupId((int) $this->_popup->getId())
                ->setEnabled((int) isset($params[$fieldName]['enable']))
                ->setName($fieldName)
                ->setLabel($params[$fieldName]['label'])
                ->setSortOrder((int) $params[$fieldName]['sort_order']);
            $result[] = $emptyField;
        }
        $sortCallback = function ($a, $b) {
            return $a['sort_order'] > $b['sort_order'] ? 1 : 0;
        };
        uasort($result, $sortCallback);
        return $result;
    }

    protected function _loadMailChimpList($data)
    {
        $mailchimpList = $this->_sourceMailchimpList->toOptionHash();
        return $this->_loadData(
            $data,
            $mailchimpList,
            \Plumrocket\Newsletterpopup\Model\MailchimpList::class
        );
    }

    protected function _loadData($data, $keys, $modelName)
    {
        $result = [];
        foreach ($keys as $key => $_) {
            if (array_key_exists($key, $data) && isset($data[$key]['enable'])) {
                $result[] = $this->_objectManager->create($modelName)->setData([
                    'popup_id'        => $this->_popup->getId(),
                    'name'            => $key,
                    'label'            => $data[$key]['label'],
                    'enable'         => (int)isset($data[$key]['enable']),
                    'sort_order'     => (int)$data[$key]['sort_order'],
                ]);
            }
        }

        $sortCallback = function ($a, $b) {
            return $a['sort_order'] > $b['sort_order'] ? 1 : 0;
        };

        uasort($result, $sortCallback);

        return $result;
    }
}
