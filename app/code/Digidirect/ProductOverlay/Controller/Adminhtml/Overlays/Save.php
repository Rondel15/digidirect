<?php

// @codingStandardsIgnoreFile

namespace Digidirect\ProductOverlay\Controller\Adminhtml\Overlays;

use Digidirect\ProductOverlay\Helper\Data;
use Digidirect\ProductOverlay\Model\Overlays;
use Magento\Framework\App\Filesystem\DirectoryList;
use Digidirect\ProductOverlay\Model\Overlay\Attribute\Source;

/**
 * Class Save
 * @package Digidirect\ProductOverlay\Controller\Adminhtml\Overlays
 */
class Save extends \Digidirect\ProductOverlay\Controller\Adminhtml\Overlays
{
    /**
     * Overlay Save Action
     *
     * @return void
     * @SuppressWarnings(Generic.Metrics.CyclomaticComplexity.MaxExceeded)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        /** @var \Magento\Framework\App\RequestInterface $request */
        $request = $this->getRequest();

        if ($request->getPostValue()) {
            /**
             * @var \Magento\Framework\Message\ManagerInterface $messageManager
             * @var \Digidirect\ProductOverlay\Helper\Data $_overlayHelper
             */
            $messageManager = $this->getMessageManager();
            $_overlayHelper = $this->_getOverlayHelper();
            $_session = $this->_getSession();
            $data = $request->getPostValue();
            $id = (int)$request->getParam(Overlays::OVERLAY_ID);

            try {
                $inputFilter = new \Magento\Framework\Filter\FilterInput(
                    ['date_from' => $this->_dateFilter, 'date_to' => $this->_dateFilter],
                    [],
                    $data
                );
                $data = $inputFilter->getUnescaped();

                /** @var \Digidirect\ProductOverlay\Model\Overlays $overlay */
                if ($id) {
                    $overlay = $this->_overlayRepository->getById($id);
                    if ($id != $overlay->getId()) {
                        throw new \Magento\Framework\Exception\LocalizedException(
                            __('The wrong overlay is specified.')
                        );
                    }
                } else {
                    $overlay = $this->_overlayFactory->create();
                }

                if (isset($data[Overlays::IS_SALE]) && $data[Overlays::IS_SALE] == Source\IsSale::YES) {
                    $salesMin = $_overlayHelper->getModuleConfig(Data::XML_PATH_ON_SALE_SALE_MIN);
                    $saleMinPercent = $_overlayHelper->getModuleConfig(Data::XML_PATH_ON_SALE_SALE_MIN_PERCENT);

                    if (!$salesMin && !$saleMinPercent) {
                        throw new \Magento\Framework\Exception\LocalizedException(
                            __(
                                'Please check general settings and try again, 
                                see Stores - Configuration - Digidirect - Product Overlays - On Sale.'
                            )
                        );
                    }
                }

                if (isset($data[Overlays::IS_NEW]) && $data[Overlays::IS_NEW] == Source\IsNew::YES) {
                    $useIsNewDates = $_overlayHelper->getModuleConfig(Data::XML_PATH_NEW_IS_NEW);
                    $useCreationDate = $_overlayHelper->getModuleConfig(Data::XML_PATH_NEW_CREATION_DATE);

                    if (!$useIsNewDates && !$useCreationDate) {
                        throw new \Magento\Framework\Exception\LocalizedException(
                            __(
                                'Please check general settings and try again,
                                see Stores - Configuration - Digidirect - Product Overlays - Is New'
                            )
                        );
                    }
                }

                if (isset($data[Overlays::CUSTOMER_GROUP_IDS])) {
                    $data[Overlays::CUSTOMER_GROUP_IDS] = $this->serializer->serialize(
                        $data[Overlays::CUSTOMER_GROUP_IDS]
                    );
                }
                /* if only one store exists */
                if (isset($data[Overlays::STORES]) && !$data[Overlays::STORES]) {
                    $data[Overlays::STORES] = 1;
                }

                if (isset($data['rule']) && isset($data['rule']['conditions'])) {
                    $data['conditions'] = $data['rule']['conditions'];

                    unset($data['rule']);

                    /** @var \Digidirect\ProductOverlay\Model\Rule $overlayRule */
                    $overlayRule = $this->_overlayRuleFactory->create();
                    $overlayRule->loadPost($data);

                    $data[Overlays::COND_SERIALIZE] = $this->serializer->serialize(
                        $overlayRule->getConditions()->asArray()
                    );
                    unset($data['conditions']);
                }

                if (!empty($data['to_time'])) {
                    $data['to_date'] = $data['to_date'] . ' ' . $data['to_time'];
                }

                if (!empty($data['to_date'])) {
                    $data['to_date'] = $this->timezone->convertToTz(
                        $data['to_date'],
                        $this->timezone->getTimezone()->getDefaultTimezone(),
                        $this->timezone->getTimezone()->getConfigTimezone()
                    );
                }

                if (!empty($data['from_time'])) {
                    $data['from_date'] = $data['from_date'] . ' ' . $data['from_time'];
                }

                if (!empty($data['from_date'])) {
                    $data['from_date'] = $this->timezone->convertToTz(
                        $data['from_date'],
                        $this->timezone->getTimezone()->getDefaultTimezone(),
                        $this->timezone->getTimezone()->getConfigTimezone()
                    );
                }

                if (isset($data[Overlays::TO_PRICE]) && !strlen($data[Overlays::TO_PRICE])) {
                    $data[Overlays::TO_PRICE] = null;
                }

                $overlay->setData($data);

                $overlay->validateTimes();

                if (!$overlay->validateDates()) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('"From Date" should be less than "To Date".')
                    );
                }

                if (!$overlay->validatePrices()) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('"From Price" should be less than "To Price".')
                    );
                }

                $_session->setPageData($overlay->getData());

                $this->_prepareForSave($overlay);

                $warningPriority = false;
                if (!$overlay->getId()) {
                    $priority = $this->_overlayRepository->getByPriority($overlay->getPos());
                    if (!empty($priority)) {
                        $warningPriority = true;
                    }
                }

                $this->_overlayRepository->save($overlay);

                if ($warningPriority) {
                    $messageManager->addWarningMessage(
                        __(
                            'An overlay with the same priority already exists. 
                            An overlay with the greater ID will be displayed'
                        )
                    );
                }

                $messageManager->addSuccessMessage(__('You saved the overlay.'));
                $_session->setPageData(false);
                $this->typeList->invalidate($this->data['invalidate_cache_types'] ?? []);

                if ($request->getParam('back')) {
                    $this->_redirect('digidirect_productoverlay/*/edit', ['id' => $overlay->getId()]);
                    return;
                }
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $messageManager->addErrorMessage($e->getMessage());
                $this->_redirect('digidirect_productoverlay/*/edit', ['id' => $id]);
                return;
            } catch (\Exception $e) {
                $messageManager->addErrorMessage(
                    __('Something went wrong while saving the item data. Please review the error log.')
                );
                $this->_logger->critical($e);

                $_session->setPageData($data);
                $this->_redirect('digidirect_productoverlay/*/edit', ['id' => $id]);
                return;
            }
        }
        $this->_redirect('digidirect_productoverlay/*/');
        return;
    }

    /**
     * @param Overlays $overlay
     * @return bool
     * @throws \Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _prepareForSave(\Digidirect\ProductOverlay\Model\Overlays $overlay)
    {
        /** @var \Digidirect\ProductOverlay\Helper\Data $_helper */
        $_helper = $this->_getOverlayHelper();

        //upload images
        $data = $this->getRequest()->getPost();

        $imagesTypes = ['prod', 'cat'];
        foreach ($imagesTypes as $type) {
            $path = $_helper->getOverlayImagePath();
            $field = $type . '_img';
            $isRemove = property_exists($data, 'remove_' . $field);

            try {
                $file = $_helper->getFileForUpload($field);
                $hasNew = $file && !empty($file->getFileExtension());

                // remove the old file
                if ($isRemove || $hasNew) {
                    $oldName = isset($data['old_' . $field]) ? $data['old_' . $field] : '';
                    if ($oldName) {
                        $overlay->setData($field, '');
                    }
                }

                // upload a new if any
                if (!$isRemove && $hasNew) {
                    $stores = $overlay->getStores();
                    if (is_array($stores)) {
                        $stores = \Magento\Store\Model\Store::DEFAULT_STORE_ID;
                    }
                    if (!$file->checkMimeType($_helper->getValidMimeTypes($stores))) {
                        throw new \Magento\Framework\Exception\LocalizedException(
                            __('Please, upload an image in correct format.')
                        );
                    }

                    $file->setAllowedExtensions($_helper->getAllowedImageExtensions($stores));
                    $file->setAllowRenameFiles(true);
                    $file->save($path);

                    $overlay->setData($field, $file->getUploadedFileName());
                }
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                throw $e;
            } catch (\Exception $e) {
                if ($e->getCode() != \Magento\MediaStorage\Model\File\Uploader::TMP_NAME_EMPTY) {
                    $this->_logger->critical($e);
                }
                $this->messageManager->addErrorMessage($e->getMessage());
                throw $e;
            }
        }

        return true;
    }
}
