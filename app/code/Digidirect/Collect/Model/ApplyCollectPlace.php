<?php
namespace Digidirect\Collect\Model;

use Digidirect\Collect\Api\ApplyCollectPlaceInterface;
use Digidirect\Collect\Api\Data\CollectPlaceInterface;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Psr\Log\LoggerInterface;

/**
 * Class ApplyCollectPlace
 *
 * @package Digidirect\Collect\Model
 */
class ApplyCollectPlace extends AbstractApplyShippingVariation implements ApplyCollectPlaceInterface
{
    /**
     * @var StorageHandler
     */
    protected $storageHandler;

    /**
     * @var Json
     */
    protected $jsonSerializer;

    /**
     * @var EventManagerInterface
     */
    protected $eventManager;

    /**
     * ApplyCollectPlace constructor.
     *
     * @param Session $session
     * @param StorageHandler $storageHandler
     * @param LoggerInterface $logger
     * @param Json $jsonSerializer
     * @param EventManagerInterface|null $eventManager
     */
    public function __construct(
        Session $session,
        StorageHandler $storageHandler,
        LoggerInterface $logger,
        Json $jsonSerializer,
        EventManagerInterface $eventManager = null
    ) {
        parent::__construct($session, $logger);
        $this->storageHandler = $storageHandler;
        $this->jsonSerializer = $jsonSerializer;
        $objectManager = ObjectManager::getInstance();
        $this->eventManager = $eventManager ?: $objectManager->get(EventManagerInterface::class);
    }

    /**
     * @param string $collectPlaceId
     * @param string $storageName
     * @return bool|string
     */
    public function applyCollectPlaceToAllItems($collectPlaceId, $storageName)
    {
        try {
            $collectPlace = $this->storageHandler->getCollectPlaceById($collectPlaceId, $storageName);
        } catch (\Exception $e) {
            return false;
        }

        $this->eventManager->dispatch(
            'digidirect_collect_before_apply_collect_place_to_items',
            [
                'quote_items' => $this->checkoutSession->getQuote()->getAllVisibleItems(),
                'collect_place' => $collectPlace
            ]
        );

        $items = $this->applyCollectParamsToAllItems($collectPlaceId, $storageName);
        if (!$items) {
            return false;
        }

        $firstItem = reset($items);

        $resultObject = new DataObject([
            'collect_place_name' => $collectPlace->getName(),
            'collect_place_address' => $collectPlace->getAddress(),
            'item_id' => $firstItem->getItemId(),
            'item_name' => $firstItem->getName()
        ]);
        $this->eventManager->dispatch(
            'digidirect_collect_apply_collect_place_to_all_items_result',
            [
                'quote_item' => $firstItem,
                'collect_place' => $collectPlace,
                'result' => $resultObject
            ]
        );

        // such format is used for handy usage from front-end
        $result[0] = $resultObject->getData();

        return $this->jsonSerializer->serialize($result);
    }

    /**
     * @return bool|CollectPlaceInterface
     */
    public function getSelectedSingleCollectPlace()
    {
        $quote = $this->checkoutSession->getQuote();
        $collectPlace = false;

        /** @var \Magento\Quote\Model\Quote\Item $item */
        foreach ($quote->getAllVisibleItems() as $item) {
            if (($collectPlaceId = $item->getCollectPlaceId())
                && ($storageName = $item->getCollectPlaceStorageName())) {
                try {
                    $collectPlace = $this->storageHandler->getCollectPlaceById($collectPlaceId, $storageName);
                    break;
                } catch (\Exception $e) {
                    $this->logger->error(
                        "Can't fetch CollectPlace with ID {$collectPlaceId} and Storage Name {$storageName}" .
                        'Error is: ' . $e->getMessage()
                    );
                    break;
                }
            }
        }

        return $collectPlace;
    }
}
