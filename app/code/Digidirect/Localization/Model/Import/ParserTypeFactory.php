<?php

namespace Digidirect\Localization\Model\Import;

class ParserTypeFactory
{
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager = null;

    /**
     * @var array
     */
    protected $types = [];

    /**
     * ParserTypeFactory constructor.
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param array $types
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        array $types = []
    ) {
        $this->objectManager = $objectManager;
        $this->types = $types;
    }

    /**
     * @param string $type
     * @return ParserProcessorInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function create($type)
    {
        $type = strtolower($type);
        if (!array_key_exists($type, $this->types) || !class_exists($this->types[$type])) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('File is not supported', $type)
            );
        }
        $adapter = $this->objectManager->get($this->types[$type]);
        if (!$adapter instanceof ParserProcessorInterface) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Parser must be an instance of \Digidirect\Localization\Model\Import\ParserProcessorInterface')
            );
        }
        return $adapter;
    }
}
