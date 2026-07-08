<?php

namespace Digidirect\AbstractGiftCard\Service\Command;

use Magento\Framework\ObjectManager\TMap;
use Digidirect\AbstractGiftCard\Service\CommandInterface;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\ObjectManager\TMapFactory;

/**
 * Class CommandPool
 * @api
 */
class CommandPool implements CommandPoolInterface
{
    /**
     * @var CommandInterface[] | TMap
     */
    private $_commands;

    /**
     * @param TMapFactory $tmapFactory
     * @param array $commands
     */
    public function __construct(
        TMapFactory $tmapFactory,
        array $commands = []
    ) {
        $this->_commands = $tmapFactory->create(
            [
                'array' => $commands,
                'type' => CommandInterface::class
            ]
        );
    }

    /**
     * Retrieves operation
     *
     * @param string $commandCode
     * @return CommandInterface
     * @throws NotFoundException
     */
    public function get($commandCode)
    {
        if (!isset($this->_commands[$commandCode])) {
            throw new NotFoundException(__('Command %1 does not exist.', $commandCode));
        }

        return $this->_commands[$commandCode];
    }
}
