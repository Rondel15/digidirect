<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_Shopbybrand
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Shopbybrand\Controller\Adminhtml\Attribute;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Page;
use Magento\Catalog\Model\Product\Attribute\Repository;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Json\Helper\Data;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\Shopbybrand\Helper\Data as HelperData;
use Mageplaza\Shopbybrand\Model\BrandFactory;

/**
 * Class Update
 * @package Mageplaza\Shopbybrand\Controller\Adminhtml\Attribute
 */
class Update extends Action
{
    /**
     * @type Data
     */
    protected $_jsonHelper;

    /**
     * @type HelperData
     */
    protected $_brandHelper;

    /**
     * @type Repository
     */
    protected $_productAttributeRepository;

    /**
     * @type BrandFactory
     */
    protected $_brandFactory;

    /**
     * @type PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @type StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Update constructor.
     *
     * @param Context $context
     * @param Data $jsonHelper
     * @param StoreManagerInterface $storeManager
     * @param Repository $productRepository
     * @param PageFactory $resultPageFactory
     * @param HelperData $brandHelper
     * @param BrandFactory $brandFactory
     */
    public function __construct(
        Context $context,
        Data $jsonHelper,
        StoreManagerInterface $storeManager,
        Repository $productRepository,
        PageFactory $resultPageFactory,
        HelperData $brandHelper,
        BrandFactory $brandFactory
    ) {
        parent::__construct($context);

        $this->_jsonHelper                 = $jsonHelper;
        $this->_brandHelper                = $brandHelper;
        $this->_productAttributeRepository = $productRepository;
        $this->_brandFactory               = $brandFactory;
        $this->_resultPageFactory          = $resultPageFactory;
        $this->_storeManager               = $storeManager;
    }

    /**
     * @return ResponseInterface|ResultInterface|void
     * @throws NoSuchEntityException
     */
    public function execute()
    {
        $optionStore    = 0;
        $check          = 0;
        $attributeCodes = [];
        $result         = ['success' => false];
        $stores         = $this->_storeManager->getStores();
        $optionId       = (int) $this->getRequest()->getParam('id');
        $scopeId        = (int) $this->getRequest()->getParam('store', 0);

        foreach ($stores as $store) {
            $attributeCodes[$store->getData('store_id')] = $this->_brandHelper->getAttributeCode(
                $store->getData('store_id')
            );
        }

        if (!isset($attributeCodes[0])) {
            $attributeCodes[0] = $this->_brandHelper->getAttributeCode(0);
        }

        foreach ($attributeCodes as $key => $attributeCode) {
            $options = $this->_productAttributeRepository->get($attributeCode)->getOptions();

            foreach ($options as $option) {
                if ((float) $option->getValue() === (float) $optionId && $scopeId == $key) {
                    $result      = ['success' => true];
                    $optionStore = $key;
                    $check       = 1;
                    break;
                }
            }

            if ($check === 1) {
                break;
            }
        }

        if ($result['success']) {
            $brand = $this->_brandFactory->create()->loadByOption($optionId, $optionStore);
            if (!$brand->getUrlKey()) {
                $brand->setUrlKey($this->_brandHelper->formatUrlKey($brand->getDefaultValue()));

                $defaultBlock = $this->_brandHelper->getBrandDetailConfig('default_block', $optionStore);
                if ($defaultBlock) {
                    $brand->setStaticBlock($defaultBlock);
                }
            }

            /** @var Page $resultPage */
            $resultPage = $this->_resultPageFactory->create();

            $result['html']     = $resultPage->getLayout()->getBlock('brand.attribute.html')
                ->setOptionData($brand->getData())
                ->toHtml();
            $result['switcher'] = $resultPage->getLayout()->getBlock('brand.store.switcher')
                ->toHtml();
        } else {
            $result['message'] = __('Attribute option does not exist.');
        }

        $this->getResponse()->representJson($this->_jsonHelper->jsonEncode($result));
    }
}
