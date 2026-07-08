<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Controller\Adminhtml\Popups;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\PageCache\Model\Cache\Type as PageCache;
use Plumrocket\Newsletterpopup\Controller\Adminhtml\Popups;
use Plumrocket\Newsletterpopup\Helper\Data;

class Mass extends Popups
{

    /**
     * @var \Magento\Framework\App\Cache\TypeListInterface
     */
    private $cacheTypeList;

    /**
     * @var \Plumrocket\Newsletterpopup\Model\PopupFactory
     */
    private $popupFactory;

    /**
     * @param \Magento\Backend\App\Action\Context            $context
     * @param \Plumrocket\Newsletterpopup\Helper\Data        $dataHelper
     * @param \Magento\Framework\App\ResourceConnection      $resource
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Plumrocket\Newsletterpopup\Model\PopupFactory $popupFactory
     */
    public function __construct(
        Context $context,
        Data $dataHelper,
        ResourceConnection $resource,
        TypeListInterface $cacheTypeList,
        \Plumrocket\Newsletterpopup\Model\PopupFactory $popupFactory
    ) {
        parent::__construct($context, $dataHelper, $resource);
        $this->cacheTypeList = $cacheTypeList;
        $this->popupFactory = $popupFactory;
    }

    /**
     * Perform mass action.
     *
     * @return void
     */
    public function execute()
    {
        $action = $this->getRequest()->getParam('action');
        $ids = $this->getRequest()->getParam('popup_id');

        if (is_array($ids) && $ids) {
            try {
                foreach ($ids as $id) {
                    switch ($action) {
                        case 'enable':
                            /** @var \Plumrocket\Newsletterpopup\Model\Popup $popup */
                            $popup = $this->popupFactory->create();
                            $popup->load($id);
                            $popup->setStatus('1');
                            $popup->save();
                            break;
                        case 'disable':
                            /** @var \Plumrocket\Newsletterpopup\Model\Popup $popup */
                            $popup = $this->popupFactory->create();
                            $popup->load($id);
                            $popup->setStatus('0');
                            $popup->save();
                            break;
                        case 'delete':
                            $this->_delete($id);
                            break;
                        case 'duplicate':
                            $this->_duplicate($id);
                            break;
                    }
                }
                $messages = [
                    'enable'    => 'Total of %1 record(s) were successfully enabled',
                    'disable'   => 'Total of %1 record(s) were successfully disabled',
                    'delete'    => 'Total of %1 record(s) were successfully deleted',
                    'duplicate' => 'Total of %1 record(s) were successfully duplicated',
                ];

                $this->messageManager->addSuccessMessage(__($messages[$action], count($ids)));
                $this->cacheTypeList->invalidate(PageCache::TYPE_IDENTIFIER);
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__($e->getMessage()));
            }
        } else {
            $this->messageManager->addErrorMessage(__('Please select item(s)'));
        }
        $this->_redirect('*/*/index');
    }
}
