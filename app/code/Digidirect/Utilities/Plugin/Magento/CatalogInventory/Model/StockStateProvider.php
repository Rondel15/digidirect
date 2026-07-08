<?php

namespace Digidirect\Utilities\Plugin\Magento\CatalogInventory\Model;

use Digidirect\Utilities\Helper\Message;
use Digidirect\Utilities\Plugin\Magento\Framework\Message\AbstractManager;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Model\StockStateProvider as StockStateProviderOrigin;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class StockState
 * @package Digidirect\Utilities\Plugin\Magento\CatalogInventory\Model
 */
class StockStateProvider extends AbstractManager
{
    const SYMBOL_PARSE_START = '{{';
    const SYMBOL_PARSE_END = '}}';

    /**
     * @var Registry
     */
    protected $_registry;

    /**
     * StockStateProvider constructor.
     * @param Registry $registry
     * @param Message $messageHelper
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Registry $registry,
        Message $messageHelper,
        StoreManagerInterface $storeManager
    ) {
        $this->_registry = $registry;
        parent::__construct($messageHelper, $storeManager);
    }

    /**
     * @param StockStateProviderOrigin $subject
     * @param \Closure $proceed
     * @param StockItemInterface $stockItem
     * @param int|float $qty
     * @param int|float $summaryQty
     * @param int|float $origQty
     * @return \Magento\Framework\DataObject
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundCheckQuoteItemQty(
        StockStateProviderOrigin $subject,
        \Closure $proceed,
        StockItemInterface $stockItem,
        $qty,
        $summaryQty,
        $origQty = 0
    ) {
        $result = $proceed($stockItem, $qty, $summaryQty, $origQty);

        /** @var Phrase $message */
        if (($message = $result->getMessage()) && $message instanceof Phrase) {
            $identifier = $message->getText();

            if (($settingsData = $this->getMessageCustomization($identifier)) && !empty($settingsData['phrase'])) {
                $replace = $this->parseIdentifier($settingsData['phrase'], $stockItem);
                $newMessage = new Phrase($replace, $message->getArguments());

                $settingsData['origin_message'] = $identifier;
                $key = $newMessage->render();
                $this->saveToRegistry($key, $settingsData);

                $result->setMessage($newMessage);
            }
        }
        return $result;
    }

    /**
     * @param array $phrase
     * @param StockItemInterface $item
     * @return mixed
     */
    public function parseIdentifier($phrase, $item)
    {
        $pattern = '~'.static::SYMBOL_PARSE_START. '(.*?)' .static::SYMBOL_PARSE_END.'~';
        $callback = function ($match) use ($item) {
            return $item->getData($match[1]) ?: $match[0];
        };
        return preg_replace_callback($pattern, $callback, $phrase);
    }

    /**
     * @param string $key
     * @param array $settingsData
     * @return void
     */
    public function saveToRegistry($key, $settingsData)
    {
        if (!$this->_registry->registry($key)) {
            $this->_registry->register($key, $settingsData);
        }
    }
}
