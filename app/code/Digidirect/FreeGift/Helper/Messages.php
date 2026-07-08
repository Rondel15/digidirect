<?php

namespace Digidirect\FreeGift\Helper;

use Magento\Framework\Registry;

class Messages extends \Magento\Framework\App\Helper\AbstractHelper
{
    const REGISTRY_KEY = 'freegift_messages';

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $_messageManager;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;

    /**
     * @var Config
     */
    protected $_configHelper;

    /**
     * Messages constructor.
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param Config $configHelper
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        Config $configHelper
    ) {
        parent::__construct($context);
        $this->_registry = $registry;
        $this->_messageManager = $messageManager;
        $this->_configHelper = $configHelper;
    }

    /**
     * @return \Magento\Framework\Message\Collection
     */
    public function clearFreeGiftMessages()
    {
        return $this->_messageManager->getMessages(true);
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return void
     */
    public function addAvailabilityError(\Magento\Catalog\Model\Product $product)
    {
        $this->showMessage(__(
            "We apologize, but your free gift <%1> is not available at the moment",
            $product->getName()
        ));
    }

    /**
     * @param string $message
     * @param bool $isError
     * @param bool $showEachTime
     * @return bool
     */
    public function showMessage($message, $isError = true, $showEachTime = false)
    {
        if (!$this->_configHelper->isDisplayErrorMessages() && $isError) {
            return false;
        }

        if (!$this->_configHelper->isDisplaySuccessMessages() && !$isError) {
            return false;
        }

        $allMessages = $this->clearFreeGiftMessages();
        foreach ($allMessages as $existingMessage) {
            if ($message == $existingMessage->getText()) {
                return false;
            }
        }

        if ($isError && $this->_request->getParam('debug')) {
            $this->_messageManager->addErrorMessage($message);
        } else {
            $freeGiftMessages = $this->_registry->registry(self::REGISTRY_KEY);
            if (!is_array($freeGiftMessages)) {
                $freeGiftMessages = [];
            }

            if (!in_array($message, $freeGiftMessages) || $showEachTime) {
                $messageType = $this->_configHelper->getMessageType();
                $messageTypeFunctionName = 'add' . ucfirst($messageType) . 'Message';
                $this->_messageManager->$messageTypeFunctionName($message);
                $freeGiftMessages[] = $message;
                $this->_registry->unregister(self::REGISTRY_KEY);
                $this->_registry->register(self::REGISTRY_KEY, $freeGiftMessages);
            }
        }

        return true;
    }
}
