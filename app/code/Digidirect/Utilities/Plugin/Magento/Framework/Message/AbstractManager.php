<?php
namespace Digidirect\Utilities\Plugin\Magento\Framework\Message;

use Digidirect\Utilities\Helper\Message;

/**
 * Class AbstractManager
 * @package Digidirect\Utilities\Plugin\Magento\Framework\Message
 */
class AbstractManager
{
    const RENDERER_IDENTIFIER = 'addExtendedMessage';

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var Message
     */
    protected $_messageHelper;

    /**
     * @param Message $_messageHelper
     * @param \Magento\Store\Model\StoreManagerInterface $_storeManager
     */
    public function __construct(
        Message $_messageHelper,
        \Magento\Store\Model\StoreManagerInterface $_storeManager
    ) {
        $this->_storeManager = $_storeManager;
        $this->_messageHelper = $_messageHelper;
    }

    /**
     * @param \Magento\Framework\Message\Manager $subject
     * @param \Magento\Framework\Phrase $message
     * @param string $type
     * @param string|null $group
     * @param string $identifier
     * @return void
     */
    public function addMessage($subject, $message, $type, $group, $identifier)
    {
        $subject->addMessage(
            $subject->createMessage($type, self::RENDERER_IDENTIFIER)
                ->setText($message)->setData(['origin_message' => $identifier]),
            $group
        );
    }

    /**
     * @param string $message
     * @return array
     */
    public function getMessageCustomization($message)
    {
        return $this->_messageHelper->getConfigValue(
            $message,
            $this->_storeManager->getStore()->getId()
        );
    }
}
