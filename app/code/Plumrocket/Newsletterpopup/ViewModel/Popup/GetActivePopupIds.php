<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2023 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\Newsletterpopup\ViewModel\Popup;

use Magento\Store\Model\StoreManagerInterface;
use Plumrocket\Newsletterpopup\Api\Data\PopupInterface;
use Plumrocket\Newsletterpopup\Helper\DateTime;
use Plumrocket\Newsletterpopup\Model\Config\Source\Method;
use Plumrocket\Newsletterpopup\Model\Config\Source\Popup\Type as PopupType;
use Plumrocket\Newsletterpopup\Model\Config\Source\Status;
use Plumrocket\Newsletterpopup\Model\ResourceModel\Popup\CollectionFactory;

/**
 * @since 4.7.0
 */
class GetActivePopupIds
{

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Plumrocket\Newsletterpopup\Model\ResourceModel\Popup\CollectionFactory
     */
    private $popupCollectionFactory;

    /**
     * @var \Plumrocket\Newsletterpopup\Helper\DateTime
     */
    private $dateTime;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface                              $storeManager
     * @param \Plumrocket\Newsletterpopup\Model\ResourceModel\Popup\CollectionFactory $popupCollectionFactory
     * @param \Plumrocket\Newsletterpopup\Helper\DateTime                             $dateTime
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        CollectionFactory $popupCollectionFactory,
        DateTime $dateTime
    ) {
        $this->storeManager = $storeManager;
        $this->popupCollectionFactory = $popupCollectionFactory;
        $this->dateTime = $dateTime;
    }

    /**
     * Retrieve active popup IDs that can be shown on the frontend.
     *
     * @return int[]
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(): array
    {
        /** @var \Plumrocket\Newsletterpopup\Model\ResourceModel\Popup\Collection $popupCollection */
        $popupCollection = $this->popupCollectionFactory->create();

        $popupCollection
            ->addFieldToFilter('status', Status::STATUS_ENABLED)
            ->addFieldToFilter(PopupInterface::TYPE, ['neq' => PopupType::WIDGET_TEMPLATE])
            ->addFieldToFilter('display_popup', ['neq' => Method::MANUALLY])
            ->addStoreFilter($this->storeManager->getStore()->getId());

        $now = $this->dateTime->format(time(), 'YYYY-MM-dd hh:mm:ss');
        $popupCollection->getSelect()->where("(`end_date` >= '$now') OR (`end_date` IS NULL)");

        return array_map('intval', $popupCollection->getAllIds());
    }
}
