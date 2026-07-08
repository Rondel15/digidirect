<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\Newsletterpopup\Model\Popup;

use Plumrocket\Newsletterpopup\Api\Data\PopupFieldDataInterface;

/**
 * @since v3.10.0
 */
class GetFields
{
    /**
     * @var \Plumrocket\Newsletterpopup\Model\ResourceModel\FormField\CollectionFactory
     */
    private $formFieldCollectionFactory;

    /**
     * Registry constructor.
     *
     * @param \Plumrocket\Newsletterpopup\Model\ResourceModel\FormField\CollectionFactory $formFieldCollectionFactory
     */
    public function __construct(
        \Plumrocket\Newsletterpopup\Model\ResourceModel\FormField\CollectionFactory $formFieldCollectionFactory
    ) {
        $this->formFieldCollectionFactory = $formFieldCollectionFactory;
    }

    /**
     * Get fields for popup.
     *
     * @param int  $popupId
     * @param bool $onlyEnabled
     * @return \Plumrocket\Newsletterpopup\Api\Data\PopupFieldDataInterface[]
     */
    public function execute(int $popupId, bool $onlyEnabled = true): array
    {
        /** @var \Plumrocket\Newsletterpopup\Model\ResourceModel\FormField\Collection $formFieldCollection */
        $formFieldCollection = $this->formFieldCollectionFactory->create();

        $formFieldCollection->addFieldToFilter(PopupFieldDataInterface::POPUP_ID, $popupId);

        if ($onlyEnabled) {
            $formFieldCollection = $formFieldCollection->addFieldToFilter(
                PopupFieldDataInterface::ENABLED,
                1
            );
        }

        $formFieldCollection->getSelect()->order(
            [PopupFieldDataInterface::SORT_ORDER, PopupFieldDataInterface::LABEL]
        );

        return $formFieldCollection->getItems();
    }

    /**
     * Get list of default popup fields.
     *
     * @return \Plumrocket\Newsletterpopup\Api\Data\PopupFieldDataInterface[]
     */
    public function default(): array
    {
        return $this->execute(0, false);
    }

    /**
     * Get active fields for popup.
     *
     * @param int $popupId
     * @return \Plumrocket\Newsletterpopup\Api\Data\PopupFieldDataInterface[]
     */
    public function onlyEnabled(int $popupId): array
    {
        return $this->execute($popupId);
    }
}
