<?php
namespace Digidirect\Utilities\Plugin\Magento\Framework\Message;

use Digidirect\Utilities\Helper\Message;
use Magento\Framework\Message\Manager as MessageManager;
use Magento\Framework\Message\MessageInterface;
use Digidirect\Utilities\Model\System\Source;

/**
 * Class Manager
 * @package Digidirect\Utilities\Plugin\Magento\Framework\Message
 */
class Manager extends AbstractManager
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;

    /**
     * Manager constructor.
     * @param Message $_messageHelper
     * @param \Magento\Store\Model\StoreManagerInterface $_storeManager
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        Message $_messageHelper,
        \Magento\Store\Model\StoreManagerInterface $_storeManager,
        \Magento\Framework\Registry $registry
    ) {
        $this->_registry = $registry;
        parent::__construct($_messageHelper, $_storeManager);
    }

    /**
     * @param MessageManager $subject
     * @param \Closure $proceed
     * @param \Magento\Framework\Phrase $message
     * @param null $group
     * @return \Closure
     */
    public function aroundAddSuccessMessage(MessageManager $subject, \Closure $proceed, $message, $group = null)
    {
        return $this->_aroundAddMessage($subject, $proceed, $message, MessageInterface::TYPE_SUCCESS, $group);
    }

    /**
     * @param MessageManager $subject
     * @param \Closure $proceed
     * @param \Magento\Framework\Phrase $message
     * @param null $group
     * @return \Closure
     */
    public function aroundAddErrorMessage(MessageManager $subject, \Closure $proceed, $message, $group = null)
    {
        return $this->_aroundAddMessage($subject, $proceed, $message, MessageInterface::TYPE_ERROR, $group);
    }

    /**
     * @param MessageManager $subject
     * @param \Closure $proceed
     * @param \Magento\Framework\Phrase $message
     * @param null $group
     * @return \Closure
     */
    public function aroundAddWarningMessage(MessageManager $subject, \Closure $proceed, $message, $group = null)
    {
        return $this->_aroundAddMessage($subject, $proceed, $message, MessageInterface::TYPE_WARNING, $group);
    }

    /**
     * @param MessageManager $subject
     * @param \Closure $proceed
     * @param \Magento\Framework\Phrase $message
     * @param null $group
     * @return \Closure
     */
    public function aroundAddNoticeMessage(MessageManager $subject, \Closure $proceed, $message, $group = null)
    {
        return $this->_aroundAddMessage($subject, $proceed, $message, MessageInterface::TYPE_NOTICE, $group);
    }

    /**
     * @param MessageManager $subject
     * @param \Closure $proceed
     * @param \Magento\Framework\Phrase $message
     * @param null $group
     * @return \Closure
     */
    public function aroundAddSuccess(MessageManager $subject, \Closure $proceed, $message, $group = null)
    {
        return $this->_aroundAddMessage($subject, $proceed, $message, MessageInterface::TYPE_SUCCESS, $group);
    }

    /**
     * @param MessageManager $subject
     * @param \Closure $proceed
     * @param \Magento\Framework\Phrase $message
     * @param null $group
     * @return \Closure
     */
    public function aroundAddError(MessageManager $subject, \Closure $proceed, $message, $group = null)
    {
        return $this->_aroundAddMessage($subject, $proceed, $message, MessageInterface::TYPE_ERROR, $group);
    }

    /**
     * @param MessageManager $subject
     * @param \Closure $proceed
     * @param \Magento\Framework\Phrase $message
     * @param null $group
     * @return \Closure
     */
    public function aroundAddWarning(MessageManager $subject, \Closure $proceed, $message, $group = null)
    {
        return $this->_aroundAddMessage($subject, $proceed, $message, MessageInterface::TYPE_WARNING, $group);
    }

    /**
     * @param MessageManager $subject
     * @param \Closure $proceed
     * @param \Magento\Framework\Phrase $message
     * @param null $group
     * @return \Closure
     */
    public function aroundAddNotice(MessageManager $subject, \Closure $proceed, $message, $group = null)
    {
        return $this->_aroundAddMessage($subject, $proceed, $message, MessageInterface::TYPE_NOTICE, $group);
    }

    /**
     * @param MessageManager $subject
     * @param \Closure $proceed
     * @param \Magento\Framework\Phrase $message |string $message
     * @param string $type
     * @param string|null $group
     * @return \Closure
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _aroundAddMessage(MessageManager $subject, \Closure $proceed, $message, $type, $group)
    {
        $identifier = is_string($message) ? $message : $message->getText();
        $settingsData = $this->getMessageCustomization($identifier);
        $isDataFromRegistry = false;

        if (!$settingsData &&
            $settingsData = $this->_registry->registry($identifier)
        ) {
            $isDataFromRegistry = true;
        }

        if ($settingsData) {
            if (!empty($settingsData['phrase']) && !$isDataFromRegistry) {
                $arguments = is_string($message) ? [] : $message->getArguments();
                $message = new \Magento\Framework\Phrase(
                    $settingsData['phrase'],
                    $arguments
                );
            }
            if ($settingsData['renderer_type_id'] != Source::TEXT_TYPE) {
                if ($isDataFromRegistry) {
                    $this->addMessage($subject, $message, $type, $group, $settingsData['origin_message']);
                    $this->_registry->unregister($identifier);
                } else {
                    $this->addMessage($subject, $message, $type, $group, $identifier);
                }
                return $proceed;
            }
        }
        return $proceed($message, $group);
    }
}
