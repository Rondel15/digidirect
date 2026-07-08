<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Model\Source\Widget;

use Plumrocket\Newsletterpopup\Api\Data\PopupInterface;
use Plumrocket\Newsletterpopup\Model\ResourceModel\Popup\CollectionFactory;

/**
 * @since 4.0.0
 */
class Forms implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var null|\Plumrocket\Newsletterpopup\Model\ResourceModel\Popup\CollectionFactory
     */
    private $popupCollectionFactory;

    /**
     * @param \Plumrocket\Newsletterpopup\Model\ResourceModel\Popup\CollectionFactory $popupCollectionFactory
     */
    public function __construct(CollectionFactory $popupCollectionFactory)
    {
        $this->popupCollectionFactory = $popupCollectionFactory;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        /** @var \Plumrocket\Newsletterpopup\Model\ResourceModel\Popup\Collection $popupCollection */
        $popupCollection= $this->popupCollectionFactory->create();

        $popupCollection
            ->addFieldToSelect(['entity_id', PopupInterface::NAME, PopupInterface::TYPE])
            ->addFieldToFilter('status', 1)
            ->addOrder(PopupInterface::TYPE)
            ->addOrder(PopupInterface::NAME);

        $options = [];
        foreach ($popupCollection->getItems() as $popup) {
            if ($popup->isModal()) {
                continue;
            }

            $options[] = [
                'value' => $popup->getId(),
                'label' => $popup->getName(),
            ];
        }

        return $options;
    }
}
