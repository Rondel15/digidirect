<?php

namespace Digidirect\AbstractGiftCard\Service\Config;

use Magento\Framework\ObjectManager\TMap;
use Magento\Framework\ObjectManager\TMapFactory;

class ValueHandlerPool implements \Digidirect\AbstractGiftCard\Service\Config\ValueHandlerPoolInterface
{
    /**
     * Default handler code
     */
    const DEFAULT_HANDLER = 'default';

    /**
     * @var ValueHandlerInterface[] | TMap
     */
    private $_handlers;

    /**
     * @param TMapFactory $tmapFactory
     * @param array $handlers
     */
    public function __construct(
        TMapFactory $tmapFactory,
        array $handlers
    ) {
        if (!isset($handlers[self::DEFAULT_HANDLER])) {
            throw new \LogicException('Default handler should be provided.');
        }

        $this->_handlers = $tmapFactory->create(
            [
                'array' => $handlers,
                'type' => ValueHandlerInterface::class
            ]
        );
    }

    /**
     * Retrieves an appropriate configuration value handler
     *
     * @param string $field
     * @return ValueHandlerInterface
     */
    public function get($field)
    {
        return isset($this->_handlers[$field])
            ? $this->_handlers[$field]
            : $this->_handlers[self::DEFAULT_HANDLER];
    }
}
