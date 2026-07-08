<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\NotFoundException;
use Plumrocket\Newsletterpopup\Model\Popup\AssignShoppingCartPriceRule;
use Plumrocket\Newsletterpopup\Model\Popup\GetActive;
use Plumrocket\Newsletterpopup\Model\Popup\GetLockedPopupIds;
use Plumrocket\Newsletterpopup\Model\PopupFactory;

/**
 * @deprecated since 4.0.0
 */
class DataEncoded extends AbstractHelper
{
    private $_popup = null;

    private $_popupFactory;

    /**
     * @var \Plumrocket\Newsletterpopup\Model\Popup\GetActive
     */
    private $getActive;

    /**
     * @var \Plumrocket\Newsletterpopup\Model\Popup\AssignShoppingCartPriceRule
     */
    private $assignShoppingCartPriceRule;

    /**
     * @var \Plumrocket\Newsletterpopup\Helper\Config
     */
    private $config;

    /**
     * @var \Plumrocket\Newsletterpopup\Model\Popup\GetLockedPopupIds
     */
    private $getLockedPopupIds;

    /**
     * @param \Magento\Framework\App\Helper\Context                               $context
     * @param \Plumrocket\Newsletterpopup\Model\PopupFactory                      $popupFactory
     * @param \Plumrocket\Newsletterpopup\Model\Popup\GetActive                   $getActive
     * @param \Plumrocket\Newsletterpopup\Model\Popup\AssignShoppingCartPriceRule $assignShoppingCartPriceRule
     * @param \Plumrocket\Newsletterpopup\Helper\Config                           $config
     * @param \Plumrocket\Newsletterpopup\Model\Popup\GetLockedPopupIds           $getLockedPopupIds
     */
    public function __construct(
        Context $context,
        PopupFactory $popupFactory,
        GetActive $getActive,
        AssignShoppingCartPriceRule $assignShoppingCartPriceRule,
        Config $config,
        GetLockedPopupIds $getLockedPopupIds
    ) {
        parent::__construct($context);
        $this->_popupFactory = $popupFactory;
        $this->getActive = $getActive;
        $this->assignShoppingCartPriceRule = $assignShoppingCartPriceRule;
        $this->config = $config;
        $this->getLockedPopupIds = $getLockedPopupIds;
    }

    /**
     * @deprecated since 4.0.0
     * @see \Plumrocket\Newsletterpopup\Model\Popup\GetActive::execute
     * @return \Plumrocket\Newsletterpopup\Api\Data\PopupInterface
     */
    public function getCurrentPopup()
    {
        if (null === $this->_popup) {
            if (!$this->config->isModuleEnabled()) {
                $item = $this->_popupFactory->create();
            } else {
                try {
                    $item = $this->getActive->execute(
                        (string) $this->_getRequest()->getParam('area'),
                        (int) $this->_getRequest()->getParam('id')
                    );
                } catch (NoSuchEntityException|NotFoundException $e) {
                    $item = $this->_popupFactory->create();
                }
            }
            // load coupon code
            $item = $this->assignShoppingCartPriceRule->execute($item);
            $this->_popup = $item;
        }

        return $this->_popup;
    }

    /**
     * @deprecated since 4.0.0
     * @see \Plumrocket\Newsletterpopup\Model\Popup\GetLockedPopupIds::execute
     * @return array
     */
    public function getLockedPopupIds()
    {
        return $this->getLockedPopupIds->execute();
    }
}
