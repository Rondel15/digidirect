<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\Newsletterpopup\Model\Popup;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\NotFoundException;
use Magento\Store\Model\StoreManagerInterface;
use Plumrocket\Newsletterpopup\Api\Data\PopupInterface;
use Plumrocket\Newsletterpopup\Api\PopupRepositoryInterface;
use Plumrocket\Newsletterpopup\Helper\Config;
use Plumrocket\Newsletterpopup\Helper\DateTime;
use Plumrocket\Newsletterpopup\Model\Config\Source\Cookies;
use Plumrocket\Newsletterpopup\Model\Config\Source\Method;
use Plumrocket\Newsletterpopup\Model\Config\Source\Popup\Type as PopupType;
use Plumrocket\Newsletterpopup\Model\Config\Source\Show;
use Plumrocket\Newsletterpopup\Model\Config\Source\Status;
use Plumrocket\Newsletterpopup\Model\PopupFactory;
use Plumrocket\Newsletterpopup\Model\Preview;
use Plumrocket\Newsletterpopup\Model\ResourceModel\Popup\CollectionFactory;

/**
 * @since 4.0.0
 */
class GetActive
{
    /**
     * @var \Plumrocket\Newsletterpopup\Model\Popup|\Plumrocket\Newsletterpopup\Api\Data\PopupInterface
     */
    private $popup;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Plumrocket\Newsletterpopup\Model\PopupFactory
     */
    private $popupFactory;

    /**
     * @var \Plumrocket\Newsletterpopup\Model\Popup\Space
     */
    private $space;

    /**
     * @var \Plumrocket\Newsletterpopup\Helper\Config
     */
    private $config;

    /**
     * @var \Plumrocket\Newsletterpopup\Model\Preview
     */
    private $preview;

    /**
     * @var \Plumrocket\Newsletterpopup\Model\ResourceModel\Popup\CollectionFactory
     */
    private $popupCollectionFactory;

    /**
     * @var \Plumrocket\Newsletterpopup\Model\Popup\AssignShoppingCartPriceRule
     */
    private $assignShoppingCartPriceRule;

    /**
     * @var \Plumrocket\Newsletterpopup\Model\Popup\GetLockedPopupIds
     */
    private $getLockedPopupIds;

    /**
     * @var \Plumrocket\Newsletterpopup\Api\PopupRepositoryInterface
     */
    private $popupRepository;

    /**
     * @var \Plumrocket\Newsletterpopup\Helper\DateTime
     */
    private $dateTime;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface                              $storeManager
     * @param \Plumrocket\Newsletterpopup\Model\PopupFactory                          $popupFactory
     * @param \Plumrocket\Newsletterpopup\Model\Popup\Space                           $space
     * @param \Plumrocket\Newsletterpopup\Helper\Config                               $config
     * @param \Plumrocket\Newsletterpopup\Model\Preview                               $preview
     * @param \Plumrocket\Newsletterpopup\Model\ResourceModel\Popup\CollectionFactory $popupCollectionFactory
     * @param \Plumrocket\Newsletterpopup\Model\Popup\AssignShoppingCartPriceRule     $assignShoppingCartPriceRule
     * @param \Plumrocket\Newsletterpopup\Model\Popup\GetLockedPopupIds               $getLockedPopupIds
     * @param \Plumrocket\Newsletterpopup\Api\PopupRepositoryInterface                $popupRepository
     * @param \Plumrocket\Newsletterpopup\Helper\DateTime                             $dateTime
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        PopupFactory $popupFactory,
        Space $space,
        Config $config,
        Preview $preview,
        CollectionFactory $popupCollectionFactory,
        AssignShoppingCartPriceRule $assignShoppingCartPriceRule,
        GetLockedPopupIds $getLockedPopupIds,
        PopupRepositoryInterface $popupRepository,
        DateTime $dateTime
    ) {
        $this->storeManager = $storeManager;
        $this->popupFactory = $popupFactory;
        $this->space = $space;
        $this->config = $config;
        $this->preview = $preview;
        $this->popupCollectionFactory = $popupCollectionFactory;
        $this->assignShoppingCartPriceRule = $assignShoppingCartPriceRule;
        $this->getLockedPopupIds = $getLockedPopupIds;
        $this->popupRepository = $popupRepository;
        $this->dateTime = $dateTime;
    }

    /**
     * @param string $area
     * @param int    $specifiedPopupId used by manual mode or during POST subscription request
     * @return \Plumrocket\Newsletterpopup\Api\Data\PopupInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute(string $area, int $specifiedPopupId = 0): PopupInterface
    {
        if ($this->preview->isEnabled()) {
            return $this->popupFactory->create();
        }

        if ($area === Show::ON_ACCOUNT_PAGES) {
            throw new NotFoundException(__('Newsletter Popup is disabled on account pages'));
        }

        if (! $this->config->isModuleEnabled()) {
            throw new NotFoundException(__('Newsletter Popup Extension is disabled'));
        }

        if (null === $this->popup) {
            if ($specifiedPopupId) {
                $popup = $this->popupRepository->getById($specifiedPopupId);
            } else {
                $lockedIds = $this->getLockedPopupIds->execute();
                $isDisabledGlobally = $this->config->getCookieUsage() === Cookies::GLOBAL && $lockedIds;
                if ($isDisabledGlobally) {
                    throw new NotFoundException(__('Newsletter Popup Extension is closed globally.'));
                }
                $popup = $this->searchPopup((int) $this->storeManager->getStore()->getId(), $lockedIds);
            }

            $this->popup = $this->assignShoppingCartPriceRule->execute($popup);
        }

        return $this->popup;
    }

    /**
     * @param int   $storeId
     * @param array $lockedIds
     * @return \Plumrocket\Newsletterpopup\Api\Data\PopupInterface|\Plumrocket\Newsletterpopup\Model\Popup
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    private function searchPopup(int $storeId, array $lockedIds): PopupInterface
    {
        $now = $this->dateTime->format(time(), 'YYYY-MM-dd hh:mm:ss');

        $orderBy = ['display_popup ASC'];

        /** @var \Plumrocket\Newsletterpopup\Model\ResourceModel\Popup\Collection $popupCollection */
        $popupCollection = $this->popupCollectionFactory->create();

        $popupCollection->addThemeData()
                        ->addFieldToFilter('status', Status::STATUS_ENABLED)
                        ->addFieldToFilter(PopupInterface::TYPE, ['neq' => PopupType::WIDGET_TEMPLATE])
                        ->addFieldToFilter('display_popup', ['neq' => Method::MANUALLY])
                        ->addStoreFilter($storeId);

        $popupCollection->getSelect()->where("(`start_date` <= '$now') OR (`start_date` IS NULL)");
        $popupCollection->getSelect()->where("(`end_date` >= '$now') OR (`end_date` IS NULL)");

        // filter disabled popups
        if ($lockedIds && $this->config->getCookieUsage() === Cookies::SEPARATE) {
            $popupCollection->getSelect()->where('`main_table`.`entity_id` NOT IN (?)', $lockedIds);
        }

        if ($orderBy) {
            $popupCollection->getSelect()->order($orderBy);
        }

        $space = $this->space->getSpace();
        $isSubscribed = $this->space->isSubscribed($space->getCustomer());

        foreach ($popupCollection->getItems() as $key => $item) {
            $hasSubscriptionRule = false !== strpos(
                $item->getData('conditions_serialized'),
                'newsleter_subscribed'
            );

            // If customer is subscribed and have not special rule, use default logic: don't show popup.
            if ($isSubscribed && ! $hasSubscriptionRule) {
                continue;
            }

            if ($item->validate($space)) {
                return $item;
            }
        }

        throw new NotFoundException(__('Not found newsletter for current user.'));
    }
}
